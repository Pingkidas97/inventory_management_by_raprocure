<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function role_mapping()
    {
        return $this->hasOne(UserRoleMapping::class);
    }

    public function role()
    {
        return $this->hasOneThrough(
            UserRole::class,
            UserRoleMapping::class,
            'user_id', // Foreign key on user_role_mappings table
            'id', // Foreign key on user_roles table
            'id', // Local key on users table
            'user_role_id' // Local key on user_role_mappings table
        );
    }


    protected $dates = ['created_at', 'updated_at'];

    // GETTER - Display format: d/m/Y
    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => \Carbon\Carbon::parse($value)->format('d/m/Y')
        );
    }

    protected function updatedAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => \Carbon\Carbon::parse($value)->format('d/m/Y')
        );
    }

    public static function decryptPassword($passphrase, $jsonString) {
        // Decode HTML entities
        $jsonString = html_entity_decode($jsonString);

        // Decode JSON string to PHP associative array
        $jsondata = json_decode($jsonString, true);
        $salt = hex2bin($jsondata['s']);
        $ct = base64_decode($jsondata['ct']);
        $iv  = hex2bin($jsondata['iv']);
        $concatedPassphrase = $passphrase.$salt;
        $md5 = array();
        $md5[0] = md5($concatedPassphrase, true);
        $result = $md5[0];
        for ($i = 1; $i < 3; $i++) {
            $md5[$i] = md5($md5[$i - 1].$concatedPassphrase, true);
            $result .= $md5[$i];
        }
        $key = substr($result, 0, 32);
        $data = openssl_decrypt($ct, 'aes-256-cbc', $key, true, $iv);
        return json_decode($data, true);
    }

    public static function attemptLogin(User $user, string $password): bool
    {
        // Check if stored password is SHA1 hash
        if (strlen($user->password) === 40 && ctype_xdigit($user->password)) {
            if (sha1($password) === $user->password) {
                // Re-hash with bcrypt
                $user->password = Hash::make($password);
                $user->save();
                // Optional: Refresh the user instance
                $user->refresh(); // ensures latest data is used
                return true;
            }
            return false;
        }

        // Standard bcrypt check
        if (Hash::check($password, $user->password)) {
            return true;
        }
        return false;
    }

    public function get_product_name_by_prod_id($prod_id)
    {
        return \App\Models\Product::find($prod_id)?->product_name;
    }

    public function get_vendor_name_by_vend_id($vend_id)
    {
        return \App\Models\VendorProduct::find($vend_id)?->store_name;
    }

    public function buyer()
    {
        return $this->hasOne(Buyer::class, 'user_id', 'id');
    }
    public function vendor()
    {
        return $this->hasOne(Vendor::class, 'user_id', 'id');
    }
    public function branchDetails()
    {
        return $this->hasMany(BranchDetail::class, 'user_id', 'id')->where('record_type', 1)->where('is_regd_address', 2);
    }
    public function vendorRegisteredBranch()
    {
        return $this->hasOne(BranchDetail::class, 'user_id', 'id')->where('record_type', 1)->where('is_regd_address', 1);
    }
    public function topManagamantDetails()
    {
        return $this->hasMany(BranchDetail::class, 'user_id', 'id')->where('record_type', 2);
    }
    public function latestPlan()
    {
        return $this->hasOne(UserPlan::class)->latestOfMany();
    }

    public function invoiceNumbers()
    {
        return $this->hasMany(InvoiceNumber::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_created_by');
    }
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_updated_by');
    }


    public function createdDivisions(): HasMany
    {
        return $this->hasMany(Division::class, 'created_by');
    }
    public function updatedDivisions(): HasMany
    {
        return $this->hasMany(Division::class, 'updated_by');
    }
   
    public function assignedVendors(): HasMany
    {
        return $this->hasMany(Vendor::class, 'assigned_manager');
    }

    public function updatedVendors(): HasMany
    {
        return $this->hasMany(Vendor::class, 'updated_by');
    }

    public function currencyDetails()
    {
        return $this->belongsTo(Currency::class, 'currency','id');
    }

    public function managedBuyers()
    {
        return $this->hasMany(Buyer::class, 'assigned_manager');
    }

    public function updatedBuyers()
    {
        return $this->hasMany(Buyer::class, 'updated_by');
    }
    public function indents()
    {
        return $this->hasMany(Indent::class, 'updated_by');
    }
    public function createdIndents()
    {
        return $this->hasMany(Indent::class, 'created_by');
    }


    public function updatedIndents()
    {
        return $this->hasMany(Indent::class, 'updated_by');
    }

}
