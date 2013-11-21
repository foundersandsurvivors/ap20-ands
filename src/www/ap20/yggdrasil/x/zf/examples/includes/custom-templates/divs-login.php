<?php
    // don't forget about this for custom templates, or errors will not show for server-side validation
    // $error is the name of the variable used with the set_rule method
    echo (isset($error) ? $error : '');
?>

<!-- elements are grouped in "rows" -->
<div class="row">

    <!-- things that need to be side-by-side go in "cells" -->
    <div class="cell"><?php echo $label_email . $email?></div>
    <div class="cell"><?php echo $label_password . $password?></div>

    <!-- once we're done with "cells" we *must* place a "clear" div -->
    <div class="clear" style="margin-bottom:10px"></div>

    <!-- on the same row, but beneath the email and the password fields,
    we place the "remember me" checkbox and attached label -->
    <div class="cell"><?php echo $remember_me_yes?></div>
    <div class="cell"><?php echo $label_remember_me_yes?></div>

    <!-- don't forget the "clear" div -->
    <div class="clear"></div>

</div>

<!-- the submit button goes in the last row; notice the "even" class which
is used to highlight even rows differently from the odd rows; also, notice
the "last" class which removes the bottom border which is otherwise present
for any row -->
<div class="row even last"><?php echo $btnsubmit?></div>
