#!/bin/bash

# Transaction Backup Script (Hourly for COD-related data)
# Run hourly via cron: 0 * * * * /path/to/scripts/backup-transactions.sh

set -e

# Configuration
BACKUP_DIR="/var/backups/gplexpres/transactions"
DB_NAME="${DB_DATABASE:-gplexpres}"
DB_USER="${DB_USERNAME:-postgres}"
DB_HOST="${DB_HOST:-localhost}"
DB_PORT="${DB_PORT:-5432}"
RETENTION_HOURS=168  # 7 days
ENCRYPTION_KEY="${BACKUP_ENCRYPTION_KEY}"

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Generate backup filename
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/transactions_backup_${TIMESTAMP}.sql.gz"
ENCRYPTED_FILE="$BACKUP_DIR/transactions_backup_${TIMESTAMP}.sql.gz.enc"

# Backup only critical transaction tables
echo "Starting transaction backup at $(date)"
PGPASSWORD="${DB_PASSWORD}" pg_dump -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" \
    --table=payment_transactions \
    --table=courier_balances \
    --table=shipments \
    --table=settlements \
    --no-owner --no-acl | gzip > "$BACKUP_FILE"

# Encrypt backup if encryption key is set
if [ -n "$ENCRYPTION_KEY" ]; then
    echo "Encrypting backup..."
    openssl enc -aes-256-cbc -salt -in "$BACKUP_FILE" -out "$ENCRYPTED_FILE" -k "$ENCRYPTION_KEY"
    rm "$BACKUP_FILE"
    BACKUP_FILE="$ENCRYPTED_FILE"
fi

# Set permissions
chmod 600 "$BACKUP_FILE"

# Remove old backups
echo "Removing backups older than $RETENTION_HOURS hours..."
find "$BACKUP_DIR" -name "transactions_backup_*.sql.gz*" -mmin +$((RETENTION_HOURS * 60)) -delete

echo "Transaction backup completed: $BACKUP_FILE"
echo "Backup size: $(du -h "$BACKUP_FILE" | cut -f1)"





