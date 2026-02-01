#!/bin/bash

# Database Restore Script
# Usage: ./restore-database.sh <backup_file>

set -e

if [ -z "$1" ]; then
    echo "Usage: $0 <backup_file>"
    echo "Example: $0 /var/backups/gplexpres/db_backup_20260130_020000.sql.gz"
    exit 1
fi

BACKUP_FILE="$1"
DB_NAME="${DB_DATABASE:-gplexpres}"
DB_USER="${DB_USERNAME:-postgres}"
DB_HOST="${DB_HOST:-localhost}"
DB_PORT="${DB_PORT:-5432}"
ENCRYPTION_KEY="${BACKUP_ENCRYPTION_KEY}"

# Check if backup file exists
if [ ! -f "$BACKUP_FILE" ]; then
    echo "Error: Backup file not found: $BACKUP_FILE"
    exit 1
fi

# Decrypt if encrypted
TEMP_FILE="$BACKUP_FILE"
if [[ "$BACKUP_FILE" == *.enc ]]; then
    if [ -z "$ENCRYPTION_KEY" ]; then
        echo "Error: Encrypted backup requires BACKUP_ENCRYPTION_KEY"
        exit 1
    fi
    TEMP_FILE="${BACKUP_FILE%.enc}"
    echo "Decrypting backup..."
    openssl enc -d -aes-256-cbc -in "$BACKUP_FILE" -out "$TEMP_FILE" -k "$ENCRYPTION_KEY"
fi

# Confirm restore
echo "WARNING: This will restore database $DB_NAME from backup."
echo "Backup file: $BACKUP_FILE"
read -p "Are you sure? Type 'yes' to continue: " confirm

if [ "$confirm" != "yes" ]; then
    echo "Restore cancelled."
    [ "$TEMP_FILE" != "$BACKUP_FILE" ] && rm -f "$TEMP_FILE"
    exit 0
fi

# Drop existing database (CAUTION: This will delete all data!)
echo "Dropping existing database..."
PGPASSWORD="${DB_PASSWORD}" psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d postgres \
    -c "DROP DATABASE IF EXISTS $DB_NAME;"

# Create new database
echo "Creating new database..."
PGPASSWORD="${DB_PASSWORD}" psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d postgres \
    -c "CREATE DATABASE $DB_NAME;"

# Restore backup
echo "Restoring backup..."
if [[ "$TEMP_FILE" == *.gz ]]; then
    gunzip -c "$TEMP_FILE" | PGPASSWORD="${DB_PASSWORD}" psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME"
else
    PGPASSWORD="${DB_PASSWORD}" psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" < "$TEMP_FILE"
fi

# Cleanup temp file
[ "$TEMP_FILE" != "$BACKUP_FILE" ] && rm -f "$TEMP_FILE"

echo "Restore completed successfully!"





