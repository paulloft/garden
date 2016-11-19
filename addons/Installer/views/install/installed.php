<div class="block-content">
    <h3>Allready installed</h3>
    <p>Garden framework allready installed</p>
</div>
<hr>
<div class="block-header">
    <div class="block-header text-center">
        <a href="/" class="btn btn-success">Main page</a>
        <?php if (\Garden\Addons::enabled('dashboard')): ?>
            <a href="/dashboard" class="btn btn-primary">Dashboard</a>
        <?php endif; ?>
    </div>
</div>