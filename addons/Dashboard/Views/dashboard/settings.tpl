<div class="content">
    <div class="block">
        <div class="block-content">
        {form}
            {form_errors}
            <div class="form-group">
                <label>{t code="Site name"}</label>
                {input type="text" name="sitename" required=true}
            </div>

            <div class="form-group">
                <label>{t code="Localization"}</label>
                {select name="locale" options=$locales}
            </div>

            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {checkbox name="debug"}
                        {t code="Enable debug messages"}
                    </label>
                </div>
            </div>

            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {checkbox name="logs"}
                        {t code="Enable error logs"}
                    </label>
                </div>
            </div>

            <p>
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {t code="Save"}</button>
            </p>
        {/form}
        </div>
    </div>
</div>