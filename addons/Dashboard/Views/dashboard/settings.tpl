<div class="content">
    <div class="block">
        <div class="block-content">
        {form}
            {form_errors}
            <div class="form-group">
                <label>{"Site name"|translate}</label>
                {input type="text" name="sitename" required=true}
            </div>

            <div class="form-group">
                <label>{"Localization"|translate}</label>
                {select name="locale" options=$locales}
            </div>

            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {checkbox name="debug"}
                        {"Enable debug messages"|translate}
                    </label>
                </div>
            </div>

            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {checkbox name="logs"}
                        {"Enable error logs"|translate}
                    </label>
                </div>
            </div>

            <p>
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {"Save"|translate}</button>
            </p>
        {/form}
        </div>
    </div>
</div>