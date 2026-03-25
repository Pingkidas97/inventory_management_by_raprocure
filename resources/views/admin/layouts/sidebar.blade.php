<div class="bg-dark border-right" id="sidebar-wrapper">
    <div class="sidebar-heading text-white bg-dark">Admin Panel</div>
    <div class="list-group list-group-flush">
        <a href="#" class="list-group-item list-group-item-action bg-dark text-white">
            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
        </a>

        <a href="{{ route('admin.divisions.index') }}" class="list-group-item list-group-item-action bg-dark text-white">
            <i class="fas fa-user-shield me-2"></i> Product Directory
        </a>

        <a href="{{ route('admin.verified-products.index') }}" class="list-group-item list-group-item-action bg-dark text-white">
            <i class="fas fa-user-shield me-2"></i> All Verified Products
        </a>

        <a href="{{ route('admin.product-approvals.index') }}" class="list-group-item list-group-item-action bg-dark text-white">
            <i class="fas fa-user-shield me-2"></i> Products for Approval
        </a>

         <a href="{{ route('admin.new-products.index') }}" class="list-group-item list-group-item-action bg-dark text-white">
            <i class="fas fa-user-shield me-2"></i> New Product Request
        </a>

        <a href="{{ route('admin.edit-products.index') }}" class="list-group-item list-group-item-action bg-dark text-white">
            <i class="fas fa-user-shield me-2"></i> Edit Product 
        </a>

        <a href="{{ route('admin.user-roles.index') }}" class="list-group-item list-group-item-action bg-dark text-white">
            <i class="fas fa-user-shield me-2"></i> User Roles
        </a>
        <a href="{{ route('admin.users.index') }}" class="list-group-item list-group-item-action bg-dark text-white">
            <i class="fas fa-users me-2"></i> Users
        </a>

        <a href="{{ route('admin.advertisement.index') }}" class="list-group-item list-group-item-action bg-dark text-white">
            <i class="fas fa-users me-2"></i> Advertisement
        </a>

        <a href="{{ route('admin.plan.index') }}" class="list-group-item list-group-item-action bg-dark text-white">
            <i class="fas fa-users me-2"></i> Plan
        </a>

        <a href="{{ route('admin.help_support.index') }}" class="list-group-item list-group-item-action bg-dark text-white">
            <i class="fas fa-users me-2"></i> Help Support
        </a>

        <a href="{{ route('admin.notification.index') }}" class="list-group-item list-group-item-action bg-dark text-white">
            <i class="fas fa-users me-2"></i> Notification
        </a>

        <a href="{{ route('admin.buyer.index') }}" class="list-group-item list-group-item-action bg-dark text-white">
            <i class="fas fa-users me-2"></i> Buyer
        </a>
        <a href="{{ route('admin.vendor.index') }}" class="list-group-item list-group-item-action bg-dark text-white">
            <i class="fas fa-users me-2"></i> Vendor
        </a>
        <a href="{{ route('admin.accounts.buyer') }}" class="list-group-item list-group-item-action bg-dark text-white">
            <i class="fas fa-users me-2"></i> Accounts Buyer
        </a>
        <a href="{{ route('admin.accounts.vendor') }}" class="list-group-item list-group-item-action bg-dark text-white">
            <i class="fas fa-users me-2"></i> Accounts Vendor
        </a>
        <a href="{{ route('admin.password.change') }}" class="list-group-item list-group-item-action bg-dark text-white">
            <i class="fas fa-users me-2"></i> Change Password
        </a>
        
    </div>
</div>