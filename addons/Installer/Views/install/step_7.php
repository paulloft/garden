    <div class="block-content">
        <h3>Instalation complete</h3>
        <p></p>


    </div>
    <hr>
    <div class="block-header text-center">
        <a href="/" class="btn btn-success">Main page</a>
        <?php if (\Garden\Addons::enabled('dashboard')): ?>
        <a href="/dashboard" class="btn btn-primary">Dashboard</a>
        <?php endif; ?>
    </div>