<div class="container full-height">
    <div class="center-wrapper">
        <div class="center-content text-center">
            <div class="row">
                <div class="col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4">
                    <div class="sitename">{config code="main.sitename"}</div>
                    <p class="mb-20">{t code="Authorization"}</p>
                    <form method="POST" action="/entry/signin{$target}" class="form-signin">
                        <fieldset>
                        {if ($message)}
                            <div class="alert alert-danger" role="alert">
                                {$message}
                            </div>
                        {/if}
                            <div class="form-group">
                                <input type="text" name="login" placeholder="{t code='Login'}" class="form-control input-lg" required autofocus />
                            </div>
                            <div class="form-group password">
                                <input type="password" name="password" placeholder="{t code='Password'}" class="form-control input-lg" required />
                                <span class="show-password" title="Показать пароль"><i class="mdi mdi-eye"></i></span>
                            </div>
                            <input type="submit" class="btn btn-success btn-block btn-lg mb-15" value="{t code='Sign in'}" />
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div> <!-- /container -->
