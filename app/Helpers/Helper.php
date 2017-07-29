<?php

namespace App\Helpers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Mail;
use Auth;
use Config;
use View;
use Input;
use session;
use Crypt;
use Hash;
use Menu;
use Html;
use Illuminate\Support\Str;
use App\User;
use Phoenix\EloquentMeta\MetaTrait; 
use Illuminate\Support\Facades\Lang;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPMailerAutoload;
use PHPMailer; 
use Course;

class Helper {

    /**
     * function used to check stock in kit
     *
     * @param = null
     */
    
    public function generateRandomString($length) {
        $key = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));

        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }

         return $key;
    } 

 
/* @method : isUserExist
    * @param : user_id
    * Response : number
    * Return : count
    */
    static public function isUserExist($user_id=null)
    {
        $user = User::where('id',$user_id)->count(); 
        return $user;
    }


/* @method : course
    * @param : cid
    * Response : number
    * Return : count
    */
    static public function getCourseById($cid=null)
    {
        $c = Course::find($cid); 
        return $c;
    }
 
/* @method : SubCousre
    * @param : scid
    * Response : number
    * Return : count
    */
    static public function getSubCourseById($scid=null)
    {
        $sc = Course::where('parent_id',$scid)->get(); 
        return $sc;
    }
 
/* @method : getpassword
    * @param : email
    * Response :  
    * Return : true or false
    */
    
    public static function getPassword(){
        $password = "";
        $user = Auth::user();
        if(isset($user)){
            $password = Auth::user()->Password;
        }
        return $password;
    }
/* @method : check mobile number
    * @param : mobile_number
    * Response :  
    * Return : true or false
    */     
   
    
    public static function FormatPhoneNumber($number){
        return preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $number). "\n";
    }
 
 
  

   /* @method : get user details
    * @param : userid
    * Response : json
    * Return : User details 
   */
   
    public static function getUserDetails($user_id=null)
    {
        $user = User::find($user_id);
        $data['userID'] = $user->userID;
        $data['firstName'] = $user->first_name;
        $data['lastName'] = $user->last_name;
       return  $data;
    }
/* @method : send Mail
    * @param : email
    * Response :  
    * Return : true or false
    */
    public  function sendMailFrontEnd2($email_content, $template)
    {        
        $email_content['verification_token'] =  Hash::make($email_content['receipent_email']);
        $email_content['email'] = isset($email_content['receipent_email'])?$email_content['receipent_email']:'';
      
        return  Mail::send('emails.'.$template, array('content' => $email_content), function($message) use($email_content)
          {
            $name = "Yellotasker";
            $message->from('admin@yellotasker.com',$name);  
            $message->to($email_content['receipent_email'])->subject($email_content['subject']);
            
          });
    } 

     public  function sendMailFrontEnd($email_content, $template)
    {        
        $email_content['email'] = isset($email_content['receipent_email'])?$email_content['receipent_email']:'palashjain71@gmail.com'; 
       // dd($email_content);
        $mail = new PHPMailer;
        $html = view::make('emails.'.$template,['content' => $email_content['data']]);
        $html = $html->render(); 

        try {
            $mail->isSMTP(); // tell to use smtp
            $mail->CharSet = "utf-8"; // set charset to utf8
             

            $mail->SMTPAuth   = true;                  // enable SMTP authentication
            $mail->Host       = "mail.infowaynic.com"; // sets the SMTP server
            $mail->Port       = 587;   
            $mail->SMTPSecure = 'false';                 // set the SMTP port for the GMAIL server
            $mail->Username   = "no-reply@infowaynic.com"; // SMTP account username
            $mail->Password   = "no-reply@123!"; 

            $mail->setFrom($email_content['data']['email'], $email_content['data']['name']);
            $mail->Subject = $email_content['subject'];
            $mail->MsgHTML($html);
            $mail->addAddress($email_content['receipent_email'], "Growsure");
            $mail->addAddress("test@mailinator.com","Growsure"); 

            //$mail->addAttachment(‘/home/kundan/Desktop/abc.doc’, ‘abc.doc’); // Optional name
            $mail->SMTPOptions= array(
            'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
            )
            );

            $mail->send();
            //echo "success";
            } catch (phpmailerException $e) {
             
            } catch (Exception $e) {
             
            }



    } 
  /* @method : send Mail
    * @param : email
    * Response :  
    * Return : true or false
    */
    public  function sendMail($email_content, $template)
    {        
          
        return  Mail::send('emails.'.$template, array('content' => $email_content), function($message) use($email_content)
          {
            $name = 'Yellotasker';
            $message->from('no-reply@admin.com',$name);  
            $message->to($email_content['receipent_email'])->subject($email_content['subject']);
            
          });
    }
     
   
     
}
