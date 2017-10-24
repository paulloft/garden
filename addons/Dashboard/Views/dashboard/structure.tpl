<div class="content">


    <div class="block block-bordered block-themed">
        <div class="block-header bg-primary">
            <h3 class="block-title">{t code="Database structure"}</h3>
        </div>

        <div class="block-content">
            {if $capturedSql}
<pre>
{foreach from=$capturedSql item=sql}{$sql}

{/foreach}
</pre>
{else}
        <div class="alert alert-info"><i class="fa fa-check"></i> {t code="Nothing to update"}</div>
{/if}
    </div>
</div>
            

    <div class="block block-bordered block-themed">
        <div class="block-header bg-primary">
            <h3 class="block-title">{t code="Permissions"}</h3>
        </div>

        <div class="block-content">
                    {if $capturePerm}
                    <div class="table-responsive">

                        <table class="table table-striped table-vcenter">
                            <thead>
                                <tr>
                                    <th>{t code="Code"}</th>
                                    <th class="text-center">{t code="Is default"}</th>
                                    <th class="text-center" style="width: 100px;">{t code="Status"}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach from=$capturePerm item=perm}
                                <tr>
                                    <td>{$perm.code}</td>
                                    <td class="text-center">{if $perm.def}Да{else}Нет{/if}</td>
                                    <td class="text-center">
                                        {if $perm.action == 'insert'}
                                        <span class="label label-success"><i class="fa fa-plus"></i> {t code="Will be addedd"}</span>
                                        {/if}
                                        {if $perm.action == 'update'}
                                        <span class="label label-info"><i class="fa fa-refresh"></i> {t code="Will be updated"}</span>
                                        {/if}
                                        {if $perm.action == 'delete'}
                                        <span class="label label-danger"><i class="fa fa-close"></i> {t code="Will be deleted"}</span>
                                        {/if}
                                    </td>
                                </tr>
                                {/foreach}
                            </tbody>
                        </table>

                    </div>
                    {else}
                    <div class="alert alert-info"><i class="fa fa-check"></i> {t code="Nothing to update"}</div>
                    {/if}
        </div>
    </div>

    <div class="block">
        <div class="block-content">
            
        <p>
            <a href="?refresh" class="btn btn-primary"><i class="fa fa-repeat"></i> {t code="Refresh structure"}</a>
            {if $capturePerm OR $capturedSql}
            <a href="?update" class="btn btn-primary"><i class="fa fa-chevron-right"></i> {t code="Start update structure"}</a>
            {/if}
        </p>
        </div>
    </div>
</div>

