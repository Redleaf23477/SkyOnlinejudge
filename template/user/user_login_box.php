<?php
if (!defined('IN_TEMPLATE')) {
    exit('Access denied');
}
?>
<script src="<?=$_E['SITEROOT']?>js/third/bignumber.min.js"></script>
<script>
$(document).ready(function()
{
    $("#loginform").submit(function(e)
    {
        e.preventDefault();

        $("#display").html('...');
        api_submit("<?=$SkyOJ->uri('user','login')?>","#loginform","#display",function(res){
            location.href = "<?=$_E['SITEROOT']?>"+res.data;
        });
        return true;
    });
});
</script>
<div class="container">
    <div class= "row">
        <div class="col-lg-offset-4 col-lg-4 login_form"><!--mask-->
            <center>
                <h3><?php echo $_E['site']['name']; ?><br><small class="login_sub_title">User Login</small></h3>
                <div class="text-right">
                    <p><i><small class="login_comment">-Programming Is the New Literacy</small></i></p>
                </div>
                
                <form role="form" action="user.php" method="post" id="loginform">
                
                    <input type="hidden" value="login" name="mod">
                    <br>
                    
                    <div class="form-group">
                    <label for="email" style = "display: block" class="login_lable_text">Nickname / Email</label>
                    <input type="text" class="textinput" id="email" name="email" placeholder="Nickname / Email" required>
                    </div>
                    
                    <div class="form-group">
                    <label for="password" style = "display: block" class="login_lable_text">PASSWORD</label>
                    <input type="password" class="textinput" name="password" placeholder="Password" required>
                    </div>
                    
                    <br>
                    <div>
                        <small><span id="display"></span></small>
                    </div>
                    <div class="form-group">
                        <button type="submit" class= "btn-grn btn-large btn-wide" style = "width:168px">
                        <b>Login</b>
                        </button>
                    </div>
                    
                </form>
                <small>OR</small>
            
                <div class = 'link-like' onclick="location.href='<?=$SkyOJ->uri('user','register')?>'">
                <u><b>Register</b></u>
                </div>
            </center>
        </div>
    </div>
</div>