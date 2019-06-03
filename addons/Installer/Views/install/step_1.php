<div class="block-content">
    <h3>Requirements</h3>
    <ul>
        <li>PHP >= 7.0</li>
        <li>ext-json</li>
        <li>ext-pdo</li>
        <li>ext-pdo_mysql</li>
        <li>lib-pcre</li>
        <li>Smarty</li>
    </ul>
    <div class="push">
    <?php if (PHP_VERSION_ID < 50400): ?>
        <div class="alert alert-danger">
            <i class="fa fa-close"></i> <b>Error:</b>
            PHP version: <?php echo phpversion(); ?>
        </div>
        <?php $errors = true; ?>
    <?php endif; ?>

    <?php if (!extension_loaded('json')): ?>
        <div class="alert alert-danger">
            <i class="fa fa-close"></i> <b>Error:</b>
            PHP extension JSON not loaded
        </div>
        <?php $errors = true; ?>
    <?php endif; ?>

    <?php if (!extension_loaded('pcre')): ?>
        <div class="alert alert-danger">
            <i class="fa fa-close"></i> <b>Error:</b>
            PHP extension PCRE not loaded
        </div>
        <?php $errors = true; ?>
    <?php endif; ?>

    <?php if (!extension_loaded('pdo')): ?>
        <div class="alert alert-danger">
            <i class="fa fa-close"></i> <b>Error:</b>
            PHP extension PDO not loaded
        </div>
        <?php $errors = true; ?>
    <?php endif; ?>

    <?php if (!class_exists('Smarty')): ?>
        <div class="alert alert-danger">
            <i class="fa fa-close"></i> <b>Error:</b>
            Package <a href="https://packagist.org/packages/smarty/smarty" target="_blank">Smarty</a> not exists.
            Please check your composer.json.
        </div>
        <?php $errors = true; ?>
    <?php endif; ?>

    <?php if (!(touch(GDN_CONF.'/.test') && file_get_contents(GDN_CONF.'/.test') !== unlink(GDN_CONF.'/.test'))): ?>
        <div class="alert alert-danger">
            <i class="fa fa-close"></i> <b>Error:</b>
            No permission to read or write the config directory <code><?php echo GDN_CONF; ?></code>
        </div>
        <?php $errors = true; ?>
    <?php endif; ?>

    <?php if (!(touch(GDN_CACHE.'/.test') && file_get_contents(GDN_CACHE.'/.test') !== unlink(GDN_CACHE.'/.test'))): ?>
        <div class="alert alert-danger">
            <i class="fa fa-close"></i> <b>Error:</b>
            No permission to read or write the cache directory <code><?php echo GDN_CACHE; ?></code>
        </div>
        <?php $errors = true; ?>
    <?php endif; ?>



    <?php if (!class_exists('\Kuria\Error\ErrorHandler')): ?>
        <div class="alert alert-warning">
            <i class="fa fa-warning"></i> <b>Warning:</b>
            Package <a href="https://packagist.org/packages/kuria/error" target="_blank">Kuria\Error</a> not exists.
            Please check your composer.json.
        </div>
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
    <div class="row">
        <div class="col-xs-8">
            <?php if ($errors): ?>
                <p><em class="text-muted">Correct all errors before continuing</em></p>
            <?php endif; ?>
        </div>
        <div class="col-xs-4 text-right">
            <a href="/install?step=2" class="btn btn-success <?php if ($errors): ?>disabled<?php endif; ?>">
                Continue <i class="fa fa-chevron-right"></i>
            </a>
        </div>
    </div>
</div>