<div class="block-content">
    <h3>Requirements</h3>
    <ul>
        <li>PHP >= 5.4</li>
        <li>ext-json</li>
        <li>ext-pdo</li>
        <li>ext-pdo_mysql</li>
        <li>lib-pcre</li>
        <li>Smarty</li>
    </ul>
    <div class="push">
    <?php if (PHP_VERSION_ID < 50400): ?>
        <div class="alert alert-danger">
            <i class="fa fa-warning"></i> PHP version: <?php echo phpversion(); ?>
        </div>
        <?php $errors = true; ?>
    <?php endif; ?>

    <?php if (!extension_loaded('json')): ?>
        <div class="alert alert-danger">
            <i class="fa fa-warning"></i> PHP extension JSON not loaded
        </div>
        <?php $errors = true; ?>
    <?php endif; ?>

    <?php if (!extension_loaded('pcre')): ?>
        <div class="alert alert-danger">
            <i class="fa fa-warning"></i> PHP extension PCRE not loaded
        </div>
        <?php $errors = true; ?>
    <?php endif; ?>

    <?php if (!extension_loaded('pdo')): ?>
        <div class="alert alert-danger">
            <i class="fa fa-warning"></i> PHP extension PDO not loaded
        </div>
        <?php $errors = true; ?>
    <?php endif; ?>

    <?php if (!class_exists('Smarty')): ?>
        <div class="alert alert-danger">
            <i class="fa fa-warning"></i>
            Class <a href="https://github.com/smarty-php/smarty" target="_blank">Smarty</a> not exists.
            Please check your composer.json file
        </div>
        <?php $errors = true; ?>
    <?php endif; ?>

    <?php if (!(touch(GDN_CONF.'/.test') && file_get_contents(GDN_CONF.'/.test') !== false && unlink(GDN_CONF.'/.test'))): ?>
        <div class="alert alert-danger">
            <i class="fa fa-warning"></i> No permission to read or write the config directory <code><?php echo GDN_CONF; ?></code>
        </div>
        <?php $errors = true; ?>
    <?php endif; ?>

    <?php if (!(touch(GDN_CACHE.'/.test') && file_get_contents(GDN_CACHE.'/.test') !== false && unlink(GDN_CACHE.'/.test'))): ?>
        <div class="alert alert-danger">
            <i class="fa fa-warning"></i> No permission to read or write the cache directory <code><?php echo GDN_CACHE; ?></code>
        </div>
        <?php $errors = true; ?>
    <?php endif; ?>

    <?php if (!$errors): ?>
        <div class="alert alert-success">
            <i class="fa fa-check"></i> All requirements are met
        </div>
    <?php endif; ?>
    </div>
</div>
<hr>
<div class="block-header">
    <div class="text-right">
        <a href="" class="btn btn-success">Continue <i class="fa fa-chevron-right"></i></a>
    </div>
</div>