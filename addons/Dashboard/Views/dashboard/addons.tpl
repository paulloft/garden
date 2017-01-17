<div class="content">
    <div class="block">
        <div class="block-content">
            <table class="table">
                <thead>
                <tr>
                    <th width="1%"></th>
                    <th width="40%">{t code="Addon name"}</th>
                    <th width="58%">{t code="Description"}</th>
                    <th width="1%">{t code="Version"}</th>
                </tr>
                </thead>
                <tbody>
                {foreach $addons as $name => $addon}
                {if valr('info.system', $addon)}{continue}{/if}
                <tr>
                    <td>
                        <label class="css-input switch switch-sm switch-success mt-0 mb-0">
                            {checkbox name=$name}
                            <span></span>
                        </label>
                    </td>
                    <td>
                        {valr('info.name', $addon, $name)}
                    </td>
                    <td>
                        {valr('info.description', $addon)}
                    </td>
                    <td>
                        {valr('info.version', $addon)}
                    </td>
                </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>
