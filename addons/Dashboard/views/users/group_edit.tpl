<div class="content">
{if $errors}{$errors}{/if}
    <form class="form-horizontal" action="/users/{if $data.id}groupedit/{$data.id}{else}groupadd{/if}" method="post" data-success-reload="1" data-success-close="1">
        <input type="hidden" name="id" value="{$data.id|format_form}">
        <div class="block block-bordered block-themed">
            <div class="block-header bg-primary">
                <h3 class="block-title">{t code="Group info"}</h3>
            </div>

            <div class="block-content">
                <div class="form-group">
                    <label class="col-xs-12">{t code="Group name"} <span class="star">*</span></label>
                    <div class="col-xs-12">
                        <input class="form-control" type="text" name="name" value="{$data.name|format_form}" required />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12">{t code="Description"}</label>
                    <div class="col-xs-12">
                        <textarea name="description" rows="5" class="form-control">{$data.description|format_form}</textarea>
                    </div>
                </div>
                {if $group.id != 1}
                <div class="form-group">
                    <div class="col-xs-12">
                        <input type="hidden" name="active" value="" />
                        <label class="css-input css-checkbox css-checkbox-sm css-checkbox-primary"><input type="checkbox" name="active" value="1" {if $data.active}checked{/if} /><span></span> {t code="Group active"}</label>
                    </div>
                </div>
                {/if}
            </div>
        </div>

        <div class="block block-bordered block-themed">
            <div class="block-header bg-primary">
                <h3 class="block-title">{t code="Group permissions"}</h3>
            </div>
            <div class="block-content">
                {foreach from=$permList key=group item=opt}
                <div class="table-responsive">
                    <table class="table table-striped checkboxed-table" data-hover="warning">
                        <thead>
                            <tr>
                                <th class="gc gc-all">{t code="permission_{$group}" default="{$group}"}</th>
                                {foreach from=$opt.columns item=column}
                                <th class="text-center gc gc-column" style="width: 100px">{t code="permission_{$column}" default="{$column}"}</th>
                                {/foreach}
                            </tr>
                        </thead>
                        <tbody>
                        {foreach from=$opt.items key=module item=perm}
                            <tr>
                                <td class="gc gc-row">{t code="permission_{$group}_{$module}" default="{$module}"}</td>
                                {foreach from=$opt.columns item=column}
                                {if $perm.$column}
                                <td class="text-center gc gc-check">
                                    <label class="css-input css-checkbox css-checkbox-sm css-checkbox-primary"><input type="checkbox" name="permission[]" value="{$perm.$column.id}" {if in_array($perm.$column.id, $data.permission) OR (!isset($data.permission) && $perm.$column.default)}checked{/if} /><span></span></label>
                                </td>
                                {else}
                                <td>&nbsp;</td>
                                {/if}
                                {/foreach}
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
                {/foreach}
            </div>
        </div>

        <div class="block">
            <div class="block-content">
                <div class="mb-20">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {t code="Save"}</button>
                    <a href="/users/groups" class="btn btn-default"><i class="fa fa-reply"></i> {t code="Cancel"}</a>
                </div>
            </div>
        </div>
    </form>
</div>