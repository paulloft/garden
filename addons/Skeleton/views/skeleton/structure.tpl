<div class="container">
    <h1>{$title}</h1>
    {if $capturedSql}
    <pre>
{foreach from=$capturedSql item=sql}{$sql}

{/foreach}
    </pre>
    {else}
    <div class="alert alert-info" role="alert">{t code="Nothing to update"}</div>
    {/if}
</div>
