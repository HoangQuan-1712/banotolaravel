# 🚀 Quick Voucher System Setup

## Step-by-Step Setup (No Errors!)

### 1. Run Migrations First
```bash
php artisan migrate
```

### 2. Run Voucher Setup
```bash
php artisan voucher:setup
```

### 3. If You Get Errors, Try Manual Setup:

#### Option A: Manual Migration + Seeder
```bash
# Run migrations
php artisan migrate

# Run just the voucher seeder
php artisan db:seed --class=VoucherSeeder
```

#### Option B: Fresh Database Setup
```bash
# Fresh migration (WARNING: This will reset your database)
php artisan migrate:fresh

# Run all seeders including vouchers
php artisan db:seed
```

## ✅ Verification

After setup, check if everything works:

```bash
# Check if tables exist
php artisan tinker
```

Then in tinker:
```php
// Check customer tiers
App\Models\CustomerTier::count(); // Should return 4

// Check vouchers  
App\Models\Voucher::count(); // Should return 21

// Check a sample voucher
App\Models\Voucher::where('type', 'tiered_choice')->first();
```

## 🎯 Quick Test

1. **Visit Dashboard**: Go to `/dashboard` 
2. **Check Tiers**: Should show "Standard Customer" initially
3. **Create Test Order**: Make a purchase to test voucher flow

## 🔧 If Still Having Issues

### Missing Tables Error?
```bash
# Check if migration files exist
ls database/migrations/*voucher*
ls database/migrations/*customer_tier*

# If missing, the files weren't created properly
```

### Permission Issues?
```bash
# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Database Connection Issues?
Check your `.env` file:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## 🎉 Success Indicators

You'll know it's working when:
- ✅ No error messages during setup
- ✅ `/dashboard` shows tier information
- ✅ Voucher selection page loads at `/checkout/vouchers/{order_id}`
- ✅ Database has `customer_tiers`, `vouchers`, and `voucher_usages` tables

## 📞 Need Help?

If you're still getting errors, the most common issues are:

1. **Migration files not found** - Check if the migration files were created
2. **Database connection** - Verify your `.env` database settings  
3. **Permission issues** - Clear all Laravel caches
4. **Missing dependencies** - Run `composer install`

**Most reliable approach**: Run migrations manually first, then the seeder!
