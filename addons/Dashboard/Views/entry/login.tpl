<!-- Login Content -->
<div class="container full-height">
    <div class="center-wrapper">
        <div class="center-content">

            <div class="row">
                <div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4">
                    <!-- Login Block -->
                    <div class="text-center mb-20">
                        <h2 class="text-white">{c('main.sitename')}</h2>
                    </div>
                    
                    <div class="block block-themed animated fadeIn b-rounded">
                        <div class="block-header bg-success b-top-rounded">
                            <h3 class="block-title">{"Authorization"|translate}</h3>
                        </div>
                        <div class="block-content">
                            <form method="post" action="/entry/login{$target}" class="form-horizontal push-5-t">
                            {if ($error)}
                                <div class="alert alert-danger" role="alert">
                                    {$error}
                                </div>
                            {/if}
                                <div class="form-group">
                                    <label class="col-xs-12" for="login-username">{'Username'|translate}</label>
                                    <div class="col-xs-12">
                                        <input class="form-control" type="text" id="login-username" name="username" placeholder="{'Username'|translate}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-xs-12" for="login-password">{'Password'|translate}</label>
                                    <div class="col-xs-12">
                                        <input class="form-control" type="password" id="login-password" name="password" placeholder="{'Password'|translate}" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-xs-12">
                                        <label class="css-input switch switch-sm switch-success">
                                            <input type="checkbox" id="login-remember" name="remember"><span></span> {"Remember me"|translate}
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-xs-12">
                                        <button class="btn btn-sm btn-success" type="submit"><i class="fa fa-arrow-right push-5-r"></i> {'Sign in'|translate}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- END Login Block -->
                </div>
            </div>

        </div>
    </div>
</div>
