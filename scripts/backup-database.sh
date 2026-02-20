#!/bin/bash

# Database Backup Script for GPL Expres
# Run daily via cron: 0 2 * * * /path/to/scripts/backup-database.sh

set -e

# Configuration
BACKUP_DIR="/var/backups/gplexpres"
DB_NAME="${DB_DATABASE:-gplexpres}"
DB_USER="${DB_USERNAME:-postgres}"
DB_HOST="${DB_HOST:-localhost}"
DB_PORT="${DB_PORT:-5432}"
RETENTION_DAYS=30
ENCRYPTION_KEY="${BACKUP_ENCRYPTION_KEY}"

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Generate backup filename
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/db_backup_${TIMESTAMP}.sql.gz"
ENCRYPTED_FILE="$BACKUP_DIR/db_backup_${TIMESTAMP}.sql.gz.enc"

# Perform backup
echo "Starting database backup at $(date)"
PGPASSWORD="${DB_PASSWORD}" pg_dump -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" \
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
echo "Removing backups older than $RETENTION_DAYS days..."
find "$BACKUP_DIR" -name "db_backup_*.sql.gz*" -mtime +$RETENTION_DAYS -delete

# Optional: Upload to S3 or offsite storage
# aws s3 cp "$BACKUP_FILE" s3://your-bucket/backups/

echo "Backup completed: $BACKUP_FILE"
echo "Backup size: $(du -h "$BACKUP_FILE" | cut -f1)"





