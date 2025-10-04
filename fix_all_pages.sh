#!/bin/bash
# Script to add APP_INIT check to all PHP files

FILES=(
    "housekeeping/jobs.php"
    "system/settings.php"
    "system/rates_simple.php"
    "rooms/transfer_history.php"
    "rooms/transfer.php"
    "rooms/move.php"
    "rooms/cleanDone.php"
    "rooms/checkoutSuccess.php"
    "rooms/checkout.php"
    "rooms/checkin.php"
    "rooms/board.php"
    "receipts/view.php"
    "receipts/history.php"
    "housekeeping/reports.php"
    "reports/bookings.php"
    "reports/occupancy.php"
    "reports/sales.php"
)

echo "This script will add APP_INIT check to all route PHP files"
echo "Please review each file manually after running this script"
echo ""
echo "Files to fix:"
printf '%s\n' "${FILES[@]}"
