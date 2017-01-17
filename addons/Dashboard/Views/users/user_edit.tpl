<div class="content">
    {form class="form-horizontal"}
        {form_errors}
        <div class="block block-bordered block-themed">
            <div class="block-header bg-primary">
                <h3 class="block-title">{t code="User info"}</h3>
            </div>
            <div class="block-content">
                <div class="form-group">
                    <label class="col-xs-12">{t code="Login"} <span class="star">*</span></label>
                    <div class="col-xs-12">
                        {input type="text" name="login" placeholder=t("Login") required=true}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12">{t code="Email"} <span class="star">*</span></label>
                    <div class="col-xs-12">
                        {input type="email" name="email" placeholder=t("E-mail") required=true}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12">{t code="User name"} </label>
                    <div class="col-xs-12">
                        {input type="text" name="name" placeholder=t("User name")}
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12">
                        <label class="css-input css-checkbox css-checkbox-sm css-checkbox-primary">
                            {checkbox name="active"}
                            <span></span> {t code="User active"}
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="block block-bordered block-themed">
            <div class="block-header bg-primary">
                <h3 class="block-title">{t code="User groups"}</h3>
            </div>
            <div class="block-content">
                <div class="form-group">
                    {foreach $groups as $group}
                        <div class="col-xs-12">
                            <label class="css-input css-checkbox css-checkbox-sm css-checkbox-primary">
                                {input type="checkbox" name="groupsID[]" value=$group.id}
                                <span></span> {$group.name}
                            </label>
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>

        <div class="block block-bordered block-themed">
            <div class="block-header bg-primary">
                <h3 class="block-title">{t code="Change password"}</h3>
            </div>
            <div class="block-content">
                <div class="form-group">
                    <label class="col-xs-12">{t code="New password"}</label>
                    <div class="col-xs-12">
                        {input type="text" name="newpassword" placeholder=t("Type a new password")}
                    </div>
                </div>
            </div>
        </div>

        <!-- save buttons -->
        <div class="block">
            <div class="block-content">
                <div class="mb-20">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {t code="Save"}</button>
                    <a href="/dashboard/users" class="btn btn-default" ><i class="fa fa-reply"></i> {t code="Cancel"}</a>
                </div>
            </div>
        </div>
        <!-- END save buttons -->
    {/form}

</form>
</div>