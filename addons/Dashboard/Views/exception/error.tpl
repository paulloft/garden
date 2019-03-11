<!-- Error Content -->
<div class="content bg-white text-center pulldown overflow-hidden">
    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            <!-- Error Titles -->
            <h1 class="font-s128 font-w300 animated {$class}">{$code}</h1>
            <h2 class="h3 font-w300 push-50 animated fadeInUp">{$subtitle}</h2>
            {if $description}<p><small class="text-muted">{$description}</small></p>{/if}
            <!-- END Error Titles -->
        </div>
    </div>
</div>
<!-- END Error Content -->

<!-- Error Footer -->
<div class="content pulldown text-muted text-center">
    {"Would you like to do?"|translate}<br>
    <a class="link-effect" href="javascript:window.history.back();">{"Go back"|translate}</a> {"or"|translate} <a class="link-effect" href="/">{"Back to main"|translate}</a>
</div>
<!-- END Error Footer -->