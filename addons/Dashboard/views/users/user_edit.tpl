<div class="content">
{if $errors}{$errors}{/if}

<!-- Notifications -->
<form class="form-horizontal" action="/dashboard/users/{if $data.id}edit/{$data.id}{else}add{/if}" method="post" data-success-reload="1" data-success-close="1">
    <input type="hidden" name="id" value="{$data.id|format_form}">
    <div class="block block-bordered block-themed">
        <div class="block-header bg-primary">
            <h3 class="block-title">{t code="User info"}</h3>
        </div>
        <div class="block-content">
            <div class="form-group">
                <label class="col-xs-12">{t code="Login"} <span class="star">*</span></label>
                <div class="col-xs-12">
                    <input class="form-control" type="text" name="login" placeholder="{t code="Login"}" value="{$data.login|format_form}" required />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12">{t code="Email"} <span class="star">*</span></label>
                <div class="col-xs-12">
                    <input class="form-control" type="email" name="email" placeholder="{t code="E-mail"}" value="{$data.email|format_form}" required />
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12">{t code="User name"} </label>
                <div class="col-xs-12">
                    <input class="form-control" type="text" name="name" placeholder="{t code="User name"}" value="{$data.name|format_form}" />
                </div>
            </div>
            <div class="form-group">
                <div class="col-xs-12">
                    <input type="hidden" name="active" value="" />
                    <label class="css-input css-checkbox css-checkbox-sm css-checkbox-primary"><input type="checkbox" name="active" value="1" {if $data.active}checked{/if} /><span></span> {t code="User active"}</label>
                </div>
            </div>
        </div>
    </div>
    <!-- END Notifications -->

    <!-- Quick Settings -->
    <div class="block block-bordered block-themed">
        <div class="block-header bg-primary">
            <h3 class="block-title">{t code="User groups"}</h3>
        </div>
        <div class="block-content">
            <div class="form-group">
                {foreach $groups as $group}
                <div class="col-xs-12">
                    <label class="css-input css-checkbox css-checkbox-sm css-checkbox-primary"><input type="checkbox" name="groupsID[]" value="{$group.id}" {if in_array($group.id, $data.groupsID)}checked{/if} /><span></span> {$group.name}</label>
                </div>
                {/foreach}
            </div>
        </div>
    </div>

    <!-- Quick Settings -->
    <div class="block block-bordered block-themed">
        <div class="block-header bg-primary">
            <h3 class="block-title">{t code="Change password"}</h3>
        </div>
        <div class="block-content">
            <div class="form-group">
                <label class="col-xs-12">{t code="New password"}</label>
                <div class="col-xs-12">
                    <input class="form-control" type="text" name="newpassword" placeholder="{t code="Type a new password"}" value="" />
                </div>
            </div>
        </div>
    </div>
    <!-- END Quick Settings -->

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

</form>
</div>