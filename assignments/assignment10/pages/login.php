<?php

/* HERE I REQUIRE AND USE THE STICKYFORM CLASS THAT DOES ALL THE VALIDATION AND CREATES THE STICKY FORM.  THE STICKY FORM CLASS USES THE VALIDATION CLASS TO DO THE VALIDATION WORK.*/
require_once('classes/StickyForm.php');
$stickyForm = new StickyForm();
/*THE INIT FUNCTION IS WRITTEN TO START EVERYTHING OFF IT IS CALLED FROM THE INDEX.PHP PAGE */
function init(){
  global $elementsArr, $stickyForm;

  /* IF THE FORM WAS SUBMITTED DO THE FOLLOWING  */
  if(isset($_POST['submit'])){

    /*THIS METHODS TAKE THE POST ARRAY AND THE ELEMENTS ARRAY (SEE BELOW) AND PASSES THEM TO THE VALIDATION FORM METHOD OF THE STICKY FORM CLASS.  IT UPDATES THE ELEMENTS ARRAY AND RETURNS IT, THIS IS STORED IN THE $postArr VARIABLE */
    $postArr = $stickyForm->validateForm($_POST, $elementsArr);

    /* THE ELEMENTS ARRAY HAS A MASTER STATUS AREA. IF THERE ARE ANY ERRORS FOUND THE STATUS IS CHANGED TO "ERRORS" FROM THE DEFAULT OF "NOERRORS".  DEPENDING ON WHAT IS RETURNED DEPENDS ON WHAT HAPPENS NEXT.  IN THIS CASE THE RETURN MESSAGE HAS "NO ERRORS" SO WE HAVE NO PROBLEMS WITH OUR VALIDATION AND WE CAN SUBMIT THE FORM */
    if($postArr['masterStatus']['status'] == "noerrors"){
      
      /*addData() IS THE METHOD TO CALL TO ADD THE FORM INFORMATION TO THE DATABASE (NOT WRITTEN IN THIS EXAMPLE) THEN WE CALL THE GETFORM METHOD WHICH RETURNS AND ACKNOWLEDGEMENT AND THE ORGINAL ARRAY (NOT MODIFIED). THE ACKNOWLEDGEMENT IS THE FIRST PARAMETER THE ELEMENTS ARRAY IS THE ELEMENTS ARRAY WE CREATE (AGAIN SEE BELOW) */
      return login($_POST);

    }
    else{
      /* IF THERE WAS A PROBLEM WITH THE FORM VALIDATION THEN THE MODIFIED ARRAY ($postArr) WILL BE SENT AS THE SECOND PARAMETER.  THIS MODIFIED ARRAY IS THE SAME AS THE ELEMENTS ARRAY BUT ERROR MESSAGES AND VALUES HAVE BEEN ADDED TO DISPLAY ERRORS AND MAKE IT STICKY */
      return getForm("",$postArr);
    }
    
  }

  /* THIS CREATES THE FORM BASED ON THE ORIGINAL ARRAY THIS IS CALLED WHEN THE PAGE FIRST LOADS BEFORE A FORM HAS BEEN SUBMITTED */
  else {
      return getForm("", $elementsArr);
    } 
}

/* THIS IS THE DATA OF THE FORM.  IT IS A MULTI-DIMENTIONAL ASSOCIATIVE ARRAY THAT IS USED TO CONTAIN FORM DATA AND ERROR MESSAGES.   EACH SUB ARRAY IS NAMED BASED UPON WHAT FORM FIELD IT IS ATTACHED TO. FOR EXAMPLE, "NAME" GOES TO THE TEXT FIELDS WITH THE NAME ATTRIBUTE THAT HAS THE VALUE OF "NAME". NOTICE THE TYPE IS "TEXT" FOR TEXT FIELD.  DEPENDING ON WHAT HAPPENS THIS ASSOCIATE ARRAY IS UPDATED.*/
$elementsArr = [
  "masterStatus"=>[
    "status"=>"noerrors",
    "type"=>"masterStatus"
  ],
	"email"=>[
		"errorMessage"=>"<span style='color: red; margin-left: 15px;'>Email must be in the standard email format.</span>",
    "errorOutput"=>"",
    "type"=>"text",
      "value"=>"sjiamjan@admin.com",
      "regex"=>"email"
  ],
  "password"=>[
		"errorMessage"=>"<span style='color: red; margin-left: 15px;'>Password must be at least 8 chars.</span>",
    "errorOutput"=>"",
    "type"=>"text",
      "value"=>"password",
      "regex"=>"password"
  ],
];

/*THIS FUNCTION CAN BE CALLED TO ADD DATA TO THE DATABASE */
function login($post){
  global $elementsArr;  
      require_once('classes/Pdo_methods.php');
    
      $pdo = new PdoMethods();
      $sql = "SELECT * FROM admins WHERE email=:email";
      $bindings = [
        [':email',$_POST['email'],'str']
      ];
      $records = $pdo->selectBinded($sql, $bindings);
      if ($records == "error") {
          echo "in error";
          return getForm("Invalid email or password", $elementsArr);
      }else if (password_verify($post['password'], $records[0]['password'])) {
          echo $records[0]['status'];
          session_start();
          $_SESSION['access'] = "accessGranted";
          if ($records[0]['status'] === 'admin') {
            $_SESSION['status'] = "admin";
            $_SESSION['name'] = "name";
          }else {
            $_SESSION['status'] = "staff";
            $_SESSION['name'] = "name";
          }
          return header('Location: index.php?page=welcome'); 
      } else {
        return getForm("Invalid email or password", $elementsArr);
      }
      
}

   

/*THIS IS THEGET FROM FUCTION WHICH WILL BUILD THE FORM BASED UPON UPON THE (UNMODIFIED OF MODIFIED) ELEMENTS ARRAY. */
function getForm($acknowledgement, $elementsArr){

global $stickyForm;

/* THIS IS A HEREDOC STRING WHICH CREATES THE FORM AND ADD THE APPROPRIATE VALUES AND ERROR MESSAGES */
$form = <<<HTML
  <div class="container mt-3">
    <h1>Login</h1>
    <form method="post" action="index.php?page=login">
    <p style='color: red;'>{$acknowledgement}</p>
    <div class="form-group mt-3">
      <label for="email">Email Address {$elementsArr['email']['errorOutput']}</label>
      <input type="text" class="form-control" id="email" name="email" value="{$elementsArr['email']['value']}"/>
    </div> 
    <div class="form-group">
      <label for="password">Password {$elementsArr['password']['errorOutput']}</label>
      <input type="password" class="form-control" id="password" name="password" value="{$elementsArr['password']['value']}" />
    </div> 
    <div class='row'>
      <button type="submit" name="submit" class="btn btn-primary ml-3">Submit</button>
    </div>
  </form>
  </div>

HTML;

/* HERE I RETURN AN ARRAY THAT CONTAINS AN ACKNOWLEDGEMENT AND THE FORM.  THIS IS DISPLAYED ON THE INDEX PAGE. */
return [$acknowledgement, $form];

}

?>