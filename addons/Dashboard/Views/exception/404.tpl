<!-- Error Content -->
<div class="content bg-white text-center pulldown overflow-hidden">
    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            <!-- Error Titles -->
            <h1 class="font-s128 font-w300 text-city animated flipInX">404</h1>
            <h2 class="h3 font-w300 push-50 animated fadeInUp">{t code="We are sorry but the page you are looking for was not found."}</h2>
            {if $description}<p><small class="text-muted">{$description}</small></p>{/if}
            <!-- END Error Titles -->
        </div>
    </div>
</div>
<!-- END Error Content -->

<!-- Error Footer -->
<div class="content pulldown text-muted text-center">
    {t code="Would you like to do?"}<br>
    <a class="link-effect" href="javascript:window.history.back();">{t code="Go back"}</a> {t code="or"} <a class="link-effect" href="/">{t code="Back to main"}</a>
</div>
<!-- END Error Footer -->