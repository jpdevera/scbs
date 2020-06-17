<!DOCTYPE html>
<html>
  <head>

  <link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>component.css">
  <link rel="stylesheet" href="<?php echo base_url().PATH_CSS ?>materialize.css"  media="screen,projection"/>
  <link rel="stylesheet" type="text/css" href="<?php echo base_url().PATH_CSS ?>material_icons.css">
  <link type="text/css" href="<?php echo base_url().PATH_CSS ?>jquery.jscrollpane.css" rel="stylesheet" media="all" />
  <link type="text/css" rel="stylesheet" href="<?php echo base_url().PATH_CSS.CSS_LOBIBOX ?>.css" />
  
  <!-- JQUERY 2.1.1+ IS REQUIRED BY MATERIALIZE TO FUNCTION -->
  <script src="<?php echo base_url().PATH_JS ?>jquery-3.1.0.min.js"></script>
  <script src="<?php echo base_url().PATH_JS ?>jquery-ui.min.js" type="text/javascript"></script>

    <!-- QUERYMINE Page Center Css -->

    <style>
            html,
        body {
            height: 100%;
        }
        html {
            display: table;
            margin: auto;
        }
        body {
            display: table-cell;
            vertical-align: middle;
        }
    </style>

    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  </head>

  <body class="cyan">
    

        <!-- Form Section -->

      <form action="" method="GET"> <!-- Change The Form Method From Here-->
    <div class="card-panel z-depth-5">

        <!-- Form Logo Section  -->

        <div class="row margin">
            <div class="col s12 m12 l12 center">
                <img src="http://localhost/scbs/uploads/core/settings/site_settings/statos_logo.ico"
                 alt="" class="responsive-img circle" style="width:100px;">
                <p>Stratos Core Banking System</p>
            </div>
        </div>

        <!-- Form Username Input Section -->

        <div class="col s12 m12 l12">
            <div class="input-field">
                <i class="material-icons prefix">person</i>
                <input type="text" id="username" name="username" required data-parsley-error-message="Please enter email address or username" placeholder="Email or usename" value=""/>
                <label for="username">Username</label>
            </div>
        </div>

        <!-- Form Password Input Section -->

        <div class="col m12 l12">
            <div class="input-field">
                <i class="material-icons prefix">lock</i>
                <!-- <input type="password" name="password" id="password"> -->
                <input type="password" id="password" name="password" placeholder="Password" data-parsley-required="true" value="" data-parsley-error-message="Please enter password"/>
                       <!--  <a href="javascript:;"><span class="show-password" id="show_password"><i class="hide_password material-icons tooltipped none"  data-tooltip = "Hide Password">visibility</i><i class="show_password material-icons tooltipped"  data-tooltip = "Show Password">visibility_off</i></span></a> -->
                <label for="password">Password</label>
            </div>
        </div>

            <!-- Form Chekbox (Remember Me) Input Section -->

        <div class="left">
            <input type="checkbox" id="checkbox">
            <label for="checkbox">Remember Me</label>
        </div>
        <br><br>

            <!-- Form Button Section  -->

        <div class="center">
            <input type="submit" value="Login" name="login" 
            class="btn waves-effect waves-light " 
            style="width:100%; ">
        </div>

            <!-- Form "Register Now" And "Forgot Password" Link Section. -->

        <div class="" style="font-size:14px;"><br>
            <!-- <a href="" class="left">Register Now!</a> -->
            <a href="" class="right ">Forgot Password?</a>
        </div><br>
    </div>
</form>



   <!-- PLATFORM SCRIPT -->
    <script src="<?php echo base_url().PATH_JS ?>constants.js"></script>
    <!-- END PLATFORM SCRIPT -->
  <!-- PLATFORM SCRIPT -->
  <script src="<?php echo base_url().PATH_JS ?>materialize.js"></script>
  <!-- END PLATFORM SCRIPT -->

  <!-- LOBIBOX SCRIPT -->
  <script src="<?php echo base_url() . PATH_JS.JS_LOBIBOX ?>.js" type="text/javascript"></script>
  <!-- END LOBIBOX SCRIPT -->
  
  <script src="<?php echo base_url().PATH_JS ?>script.js"></script>
  <script src="<?php echo base_url().PATH_JS ?>common.js"></script>
  <script src="<?php echo base_url().PATH_JS ?>auth.js"></script>
  <script src="<?php echo base_url().PATH_JS ?>parsley.min.js" type="text/javascript"></script>
  
  <!-- OWL CAROUSEL SCRIPT -->
  <link href="<?php echo base_url().PATH_CSS ?>owl.carousel.css" rel="stylesheet" />
  <link href="<?php echo base_url().PATH_CSS ?>owl.theme.css" rel="stylesheet" />
  <script src="<?php echo base_url().PATH_JS ?>owl.carousel.js"></script>
  <!-- END OWL CAROUSEL SCRIPT -->
  
  <!-- JSCROLLPANE SCRIPT -->
  <script type="text/javascript" src="<?php echo base_url().PATH_JS ?>jquery.mousewheel.js"></script>
  <script type="text/javascript" src="<?php echo base_url().PATH_JS ?>jquery.jscrollpane.js"></script>

  <script type="text/javascript" src="<?php echo base_url() . PATH_JS ?>moment.js"></script>
  <!-- END JSCROLLPANE SCRIPT -->

  <!-- BLOCK UI SCRIPT -->
    <script src="<?php echo base_url() . PATH_JS ?>jquery.blockUI.js" type="text/javascript"></script>
    <!-- END BLOCK UI SCRIPT -->
  
  <!-- POPMODAL SCRIPT -->
  <link type="text/css" href="<?php echo base_url().PATH_CSS ?>popModal.css" rel="stylesheet" media="all" />
  <script type="text/javascript" src="<?php echo base_url().PATH_JS ?>popModal.min.js"></script>
  <!-- END POPMODAL SCRIPT -->
  
  <script src="<?php echo base_url().PATH_JS ?>initializations.js" type="text/javascript"></script>

  <script src="<?php echo base_url() . PATH_JS ?>initial.min.js" type="text/javascript"></script>
  <script src="<?php echo base_url() . PATH_JS ?>parsley_extend.js" type="text/javascript"></script>

  <link type="text/css" href="<?php echo base_url().PATH_CSS ?>hideShowPassword.css" rel="stylesheet" media="all" />
  <script type="text/javascript" src="<?php echo base_url().PATH_JS ?>hideShowPassword.min.js"></script>
  </body>
</html>