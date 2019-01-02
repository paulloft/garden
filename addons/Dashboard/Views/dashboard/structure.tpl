<div class="content">


    <div class="block block-bordered block-themed">
        <div class="block-header bg-primary">
            <h3 class="block-title">{"Database structure"|translate}</h3>
        </div>

        <div class="block-content">
            {if $capturedSql}
<pre>
{foreach from=$capturedSql item=sql}{$sql}

{/foreach}
</pre>
{else}
        <div class="alert alert-info"><i class="fa fa-check"></i> {"Nothing to update"|translate}</div>
{/if}
    </div>
</div>
            

    <div class="block block-bordered block-themed">
        <div class="block-header bg-primary">
            <h3 class="block-title">{"Permissions"|translate}</h3>
        </div>

        <div class="block-content">
                    {if $capturePerm}
                    <div class="table-responsive">

                        <table class="table table-striped table-vcenter">
                            <thead>
                                <tr>
                                    <th>{"Code"|translate}</th>
                                    <th class="text-center">{"Is default"|translate}</th>
                                    <th class="text-center" style="width: 100px;">{"Status"|translate}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach from=$capturePerm item=perm}
                                <tr>
                                    <td>{$perm.code}</td>
                                    <td class="text-center">{if $perm.def}Да{else}Нет{/if}</td>
                                    <td class="text-center">
                                        {if $perm.action == 'insert'}
                                        <span class="label label-success"><i class="fa fa-plus"></i> {"Will be addedd"|translate}</span>
                                        {/if}
                                        {if $perm.action == 'update'}
                                        <span class="label label-info"><i class="fa fa-refresh"></i> {"Will be updated"|translate}</span>
                                        {/if}
                                        {if $perm.action == 'delete'}
                                        <span class="label label-danger"><i class="fa fa-close"></i> {"Will be deleted"|translate}</span>
                                        {/if}
                                    </td>
                                </tr>
                                {/foreach}
                            </tbody>
                        </table>

                    </div>
                    {else}
                    <div class="alert alert-info"><i class="fa fa-check"></i> {"Nothing to update"|translate}</div>
                    {/if}
        </div>
    </div>

    <div class="block">
        <div class="block-content">
            
        <p>
            <a href="?refresh" class="btn btn-primary"><i class="fa fa-repeat"></i> {"Refresh structure"|translate}</a>
            {if $capturePerm OR $capturedSql}
            <a href="?update" class="btn btn-primary"><i class="fa fa-chevron-right"></i> {"Start update structure"|translate}</a>
            {/if}
        </p>
        </div>
    </div>
</div>

