<div class="content">
    {form class="form-horizontal"}
        {form_errors}
        <div class="block block-bordered block-themed">
            <div class="block-header bg-primary">
                <h3 class="block-title">{"User info"|translate}</h3>
            </div>
            <div class="block-content">
                <div class="form-group">
                    <label class="col-xs-12">{"Login"|translate} <span class="star">*</span></label>
                    <div class="col-xs-12">
                        {input type="text" name="login" placeholder={"Login"|translate} required=true}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12">{"Email"|translate} <span class="star">*</span></label>
                    <div class="col-xs-12">
                        {input type="email" name="email" placeholder={"E-mail"|translate} required=true}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12">{"User name"|translate} </label>
                    <div class="col-xs-12">
                        {input type="text" name="name" placeholder={"User name"|translate}}
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12">
                        <label class="css-input css-checkbox css-checkbox-sm css-checkbox-primary">
                            {checkbox name="active"}
                            <span></span> {"User active"|translate}
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="block block-bordered block-themed">
            <div class="block-header bg-primary">
                <h3 class="block-title">{"User groups"|translate}</h3>
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
                <h3 class="block-title">{"Change password"|translate}</h3>
            </div>
            <div class="block-content">
                <div class="form-group">
                    <label class="col-xs-12">{"New password"|translate}</label>
                    <div class="col-xs-12">
                        {input type="text" name="newpassword" placeholder={"Type a new password"|translate}}
                    </div>
                </div>
            </div>
        </div>

        <!-- save buttons -->
        <div class="block">
            <div class="block-content">
                <div class="mb-20">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {"Save"|translate}</button>
                    <a href="/dashboard/users" class="btn btn-default" ><i class="fa fa-reply"></i> {"Cancel"|translate}</a>
                </div>
            </div>
        </div>
        <!-- END save buttons -->
    {/form}

</form>
</div>