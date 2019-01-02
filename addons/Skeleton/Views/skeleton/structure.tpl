<div class="container">
    <h1>{$title}</h1>
    {if $capturedSql}
    <pre>
{foreach from=$capturedSql item=sql}{$sql}

{/foreach}
    </pre>
    {else}
    <div class="alert alert-info" role="alert">{"Nothing to update"|translate}</div>
    {/if}
    <a href="?refresh" class="btn btn-primary">{"Refresh structure"|translate}</a>
    <a href="?update" class="btn btn-primary">{"Start update structure"|translate}</a>
</div>

