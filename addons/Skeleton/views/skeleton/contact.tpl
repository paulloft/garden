<div class="container">
    <h1>Contact form</h1>
    {form}
        {form_errors}
        <div class="form-group">
            <label>Your name *</label>
            {input type="text" name="name"}
        </div>

        <div class="form-group">
            <label>Your email</label>
            {input type="text" name="email"}
        </div>

        <div class="form-group">
            <label>Your message</label>
            {textarea name="message"}
        </div>

        <button type="submit" class="btn btn-primary">submit</button>
    {/form}

</div><!-- /.container -->