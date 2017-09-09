<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Log\Writer;
use Monolog\Logger as Monolog;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests; 
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Encryption\DecryptException;
use Config,Mail,View,Redirect,Validator,Response; 
use Auth,Crypt,okie,Hash,Lang,JWTAuth,Input,Closure,URL; 
use JWTExceptionTokenInvalidException; 
use App\Helpers\Helper as Helper;
use App\User; 
use App\ContactUs;
use App\Course;
use App\Syllabus;
use App\Blogs;


class ApiController extends Controller
{
    
   /* @method : validateUser
    * @param : email,password,firstName,lastName
    * Response : json
    * Return : token and user details
    * Author : kundan Roy
    * Calling Method : get  
    */

    public function __construct(Request $request) {

        if ($request->header('Content-Type') != "application/json")  {
            $request->headers->set('Content-Type', 'application/json');
        }
        $user_id =  $request->input('userID');
       
    } 

    public function validateUser(Request $request,User $user){

        $input['first_name']    = $request->input('first_name');
        $input['last_name']     = $request->input('last_name'); 
        $input['email']         = $request->input('email'); 
        $input['password']      = Hash::make($request->input('password'));
          //Server side valiation
        if($request->input('user_id')){
            $validator = Validator::make($request->all(), [
                  
            ]); 
        } 
        else{
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:users' 
            ]); 
        }
       
        // Return Error Message
        if ($validator->fails()) {
                    $error_msg  =   [];
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }
                            
            return Response::json(array(
                'status' => 0,
                'message' => $error_msg[0],
                'data'  =>  ''
                )
            );
        }

       

        return response()->json(
                            [ 
                                "status"=>1,
                                "message"=>"User validated successfully.",
                                'data'=>$request->all()
                            ]
                        );  
    }   
    public function contactUs(Request $request,User $user)
    {   

        $input['name']    = $request->input('name'); 
        $input['email']   = $request->input('email');  
        $input['subject'] = ($request->input('subject'))?$request->input('subject'):'';
        $input['message'] = $request->input('message'); 
         
         //Server side valiation
        $validator = Validator::make($request->all(), [
           'email' => 'required|email',
            'name' => 'required',
            'subject' => 'required',
            'message' => 'required',

        ]);
        /** Return Error Message **/
        if ($validator->fails()) {
                    $error_msg  =   [];
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }
                            
            return Response::json(array(
                'status' => 0,
                'message' => $error_msg[0],
                'data'  =>  ''
                )
            );
        }  
        
        $helper = new Helper;
        /** --Create USER-- **/
      
        $subject = "New Enquiry mail!";

        $reciver_email = 'palashjain71@gmail.com';

        $email_content = [
                'receipent_email'=> $reciver_email,
                'subject'=>$subject,
                'greeting'=> 'Enquiry',
                'name'=> 'Growsure',
                'data' => $request->all()
                ];

        $verification_email = $helper->sendMailFrontEnd($email_content,'contactus');


        $user = new ContactUs;
        $user->name =  $request->get('name');
        $user->email = $request->get('email');
        $user->message = $request->get('message');
        $user->subject = $request->get('subject');
        $user->save();

       
        return response()->json(
                            [ 
                                "status"=>1,
                                "code" => 200,
                                "message"=>"Thank you for contacting us.",
                                'data'=>$request->all()
                            ]
                        );
    }

   /* @method : register
    * @param : email,password,deviceID,firstName,lastName
    * Response : json
    * Return : token and user details
    * Author : kundan Roy
    * Calling Method : get  
    */

    public function signup(Request $request,User $user)
    {   

        $input['first_name']    = $request->input('firstName');
        $input['last_name']     = $request->input('lastName'); 
        $input['email']         = $request->input('email'); 
        $input['password']      = Hash::make($request->input('password'));
         
        if($request->input('user_id')){
            $u = $this->updateProfile($request,$user);
            return $u;
        } 

        //Server side valiation
        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
           'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);
        /** Return Error Message **/
        if ($validator->fails()) {
                    $error_msg  =   [];
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }
                            
            return Response::json(array(
                'status' => 0,
                'message' => $error_msg[0],
                'data'  =>  ''
                )
            );
        }  
        User::create($input);
        
        return response()->json(
                            [ 
                                "status"=>1,
                                "message"=>"Thank you for registration",
                                'data'=>$request->except('password')
                            ]
                        );
    }

/* @method : update User Profile
    * @param : email,password,deviceID,firstName,lastName
    * Response : json
    * Return : token and user details
    * Author : kundan Roy
    * Calling Method : get  
    */
    public function updateProfile(Request $request,User $user)
    {       
        if(!Helper::isUserExist($request->get('user_id')))
        {
            return Response::json(array(
                'status' => 0,
                'message' => 'Invalid user Id!',
                'data'  =>  ''
                )
            );
        } 
        $user = User::find($request->get('user_id')); 
        $role_type  = $user->role_type;

        $data = [
                    'user_id'=>$user->id,
                    'first_name'=>$user->first_name,
                    'last_name'=>$user->first_name,
                    'email'=>$user->email,
                    'role_type' => $user->role_type
                ];
         
        foreach ($request->all() as $key => $value) {
             if($key=="email" || $key=="user_id")
             {
                continue;
             }else{
               $user->$key=$value; 
             }
        }

        try{
            $user->save();
            $status = 1;
            $code  = 200;
            $message ="Profile updated successfully";
        }catch(\Exception $e){
            $status = 0;
            $code  = 500;
            $message =$e->getMessage();
        }

        return response()->json(
                            [ 
                            "status" =>$status,
                            'code'   => $code,
                            "message"=> $message,
                            'data'=>$data
                            ]
                        );
         
    }

   /* @method : login
    * @param : email,password and deviceID
    * Response : json
    * Return : token and user details
    * Author : kundan Roy   
    */
    public function login(Request $request)
    {    
        $input = $request->all();
        if (!$token = JWTAuth::attempt(['email'=>$request->get('email'),'password'=>$request->get('password')])) {
            return response()->json([ "status"=>0,"message"=>"Invalid email or password. Try again!" ,'data' => '' ]);
        }
         
        $data = JWTAuth::toUser($token);  

        return response()->json([ "status"=>1,"code"=>200,"message"=>"Successfully logged in." ,'data' => $data ]);

    } 
   

    public function createCourse( Request $request , Course $course)
    {
         
        $course = new Course;
        $course->main_course = $request->get('main_course');
         //Server side valiation
        $validator = Validator::make($request->all(), [
           'main_course' => 'required|unique:courses'
        ]);
        /** Return Error Message **/
        if ($validator->fails()) {
                    $error_msg  =   [];
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }
                            
            return Response::json(array(
                'status' => 0,
                'message' => $error_msg[0],
                'data'  =>  ''
                )
            );
        }  


        $course->save(); 
        $main_course_id =  $course->id;
        
        $sub_course = $request->get('sub_course');  

        foreach ($sub_course as $key => $result) {

            $course = new Course;
            $course->main_course =  $request->get('main_course');
            $course->sub_course  = $result;
            $course->parent_id = $main_course_id;
            $course->save();
        }

        $courses =  Course::where('parent_id',$main_course_id)->get();

        $data['main_course'] = $request->get('main_course');

        foreach ($courses as $key => $value) {

            $data['sub_course'][] = [ 'id' => $value->id, 'name' => $value->sub_course];
        }

        
       return response()->json(
                    [   
                        "status"    =>  1,
                        "code" => 200,
                        "message"   =>  "Course Created" ,
                        'data'      =>  $data
                    ]
                ); 
    }

    public function getCourse( Request $request , Course $course,$id=null)
    {
        $courses =  Course::where('parent_id',0)->where(function($q)use($id){
            if($id){
                 $q->where('id',$id);
            }
           
        })->get();

        $result = [];
        foreach ($courses as $key => $value) {
            $subcourses =  Course::where('parent_id',$value->id)->get();
            $data['main_course_id'] = $value->id;
            $data['main_course'] = $value->main_course;
            
            foreach ($subcourses as $key => $subc) {
                $data['sub_course'][] = [ 'sub_course_id' => $subc->id, 'name' => $subc->sub_course];
            }

            $result[] = $data;
            $data['sub_course'] = [];
            
        }
       return response()->json(
                    [   
                        "status"    =>  1,
                        "code" => 200,
                        "message"   =>  "Course found" ,
                        'data'      =>  $result
                    ]
                ); 
    }

    public function createCourseDetails( Request $request , Course $course)
    {
        
        $main_course_id = $request->get('main_course_id');
        $sub_course_id  = $request->get('sub_course_id');

        $course = Course::where('id',$sub_course_id)
                            ->where('parent_id',$main_course_id)
                            ->first();

        if(count($course)==0) {
            return response()->json(
                    [   
                        "status"    =>  0,
                        "code" => 500,
                        "message"   =>  "Invalid course id" ,
                        'data'      =>  []
                    ]
                ); 
        }                    

        $course->description = $request->get('description');
        $course->image = $request->get('image');
        $course->course_prerequisites= $request->get('course_prerequisites');
        $course->course_duration = $request->get('course_duration');
        $course->training_highlights = $request->get('training_highlights');
        $course->general_info = json_encode($request->get('general_info'));
        $course->save();


        $syllabus = new Syllabus;
        $syllabus->chapter_name = $request->get('chapter_name');
        $syllabus->chapter_topic = $request->get('chapter_topic');
        $syllabus->sub_course_id = $request->get('sub_course_id');
        $syllabus->course_id = $request->get('main_course_id');
        $syllabus->description = $request->get('description');
        $syllabus->save();

         $courses = Course::leftjoin('syllabus', 'courses.id', '=', 'syllabus.sub_course_id')
                            ->where('courses.id',$sub_course_id) 
                            ->first(); 

        
       return response()->json(
                    [   
                        "status"    =>  1,
                        "code" => 200,
                        "message"   =>  "Course found" ,
                        'data'      =>  $courses
                    ]
                ); 
    }


    public function getCourseDetail( Request $request , Course $course,$id=null)
    {
        $sub_course_id = $request->get('sub_course_id');
    	$courses = Course::leftjoin('syllabus', 'courses.id', '=', 'syllabus.sub_course_id')
                            ->where('courses.id',$id) 
                            ->select('courses.*', 'syllabus.description')
                            ->first();  
        if($courses!=null){
        	$msg = "Record found";
        	$code = 200;
        	$status =1;
            $data = $courses;
        }else{
        	$msg = "record not found";
        	$code = 404;
        	$status =0;
            $data = ['sub_course_id'=>$id];
        }


    	 return response()->json(
                    [   
                        "status"    =>  $status,
                        "code"      =>  $code,
                        "message"   =>  $msg,
                        'data'      =>  $data
                    ]
                ); 


    }
 
   /* @method : Email Verification
    * @param : token_id
    * Response : json
    * Return :token and email 
   */
   
    public function emailVerification(Request $request)
    {
        $verification_code = $request->input('verification_code');
        $email    = $request->input('email');

        if (Hash::check($email, $verification_code)) {
           $user = User::where('email',$email)->get()->count();
           if($user>0)
           {
              User::where('email',$email)->update(['status'=>1]);  
           }else{
            echo "Verification link is Invalid or expire!"; exit();
                return response()->json([ "status"=>0,"message"=>"Verification link is Invalid!" ,'data' => '']);
           }
           echo "Email verified successfully."; exit();  
           return response()->json([ "status"=>1,"message"=>"Email verified successfully." ,'data' => '']);
        }else{
            echo "Verification link is Invalid!"; exit();
            return response()->json([ "status"=>0,"message"=>"Verification link is invalid!" ,'data' => '']);
        }
    }
   
   /* @method : logout
    * @param : token
    * Response : "logout message"
    * Return : json response 
   */
    public function logout(Request $request)
    {   
        $token = $request->input('token');
         
        JWTAuth::invalidate($request->input('token'));

        return  response()->json([ 
                    "status"=>1,
                    "code"=> 200,
                    "message"=>"You've successfully signed out.",
                    'data' => ""
                    ]
                );
    }
   /* @method : forget password
    * @param : token,email
    * Response : json
    * Return : json response 
    */
    public function forgetPassword(Request $request)
    {  
        $email = $request->input('email');
        //Server side valiation
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        $helper = new Helper;
       
        if ($validator->fails()) {
            $error_msg  =   [];
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }
                            
            return Response::json(array(
                'status' => 0,
                'message' => $error_msg[0],
                'data'  =>  ''
                )
            );
        }

        $user =   User::where('email',$email)->get();

        if($user->count()==0){
            return Response::json(array(
                'status' => 0,
                'message' => "Oh no! The address you provided isn't in our system",
                'data'  =>  ''
                )
            );
        }
        $user_data = User::find($user[0]->userID);
        $temp_password = Hash::make($email);
       
        
      // Send Mail after forget password
        $temp_password =  Hash::make($email);
 
        $email_content = array(
                        'receipent_email'   => $request->input('email'),
                        'subject'           => 'Reset account password link!',
                        'first_name'        => $user[0]->first_name,
                        'temp_password'     => $temp_password,
                        'encrypt_key'       => Crypt::encrypt($email),
                        'greeting'          => 'Yellotasker'

                    );
        $helper = new Helper;
        $email_response = $helper->sendMail(
                                $email_content,
                                'forgot_password_link'
                            ); 
       
       return   response()->json(
                    [ 
                        "status"=>1,
                        "code"=> 200,
                        "message"=>"Reset password link has sent. Please check your email.",
                        'data' => ''
                    ]
                );
    }

   /* @method : change password
    * @param : token,oldpassword, newpassword
    * Response : "message"
    * Return : json response 
   */
    public function changePassword(Request $request)
    {   
        $user = JWTAuth::toUser($request->input('deviceToken'));
        $user_id = $user->userID; 
        $old_password = $user->password;
     
        $validator = Validator::make($request->all(), [
            'oldPassword' => 'required',
            'newPassword' => 'required|min:6'
        ]);
        // Return Error Message
        if ($validator->fails()) {
            $error_msg  =   [];
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }
                            
            return Response::json(array(
                'status' => 0,
                'message' => $error_msg[0],
                'data'  =>  ''
                )
            );
        }

         
        if (Hash::check($request->input('oldPassword'),$old_password)) {

           $user_data =  User::find($user_id);
           $user_data->password =  Hash::make($request->input('newPassword'));
           $user_data->save();
           return  response()->json([ 
                    "status"=>1,
                    "code"=> 200,
                    "message"=>"Password changed successfully.",
                    'data' => ""
                    ]
                );
        }else
        {
            return Response::json(array(
                'status' => 0,
                'message' => "Old password mismatch!",
                'data'  =>  ''
                )
            );
        }         
    
    }
    
  


    public function deleteCourse(Request $request,$id)
    {
 
        $deleteCourse = Course::where('id',$id)->get();
        if($deleteCourse->count()){
            $deleteCourse = Course::where('id',$id)->delete();
            $deleteCourse = Course::where('parent_id',$id)->delete();
            return  response()->json([ 
                    "status"=>1,
                    "code"=> 200,
                    "message"=>"Cource deleted successfully.",
                    'data' => ''
                   ]
                );

        } else {
            return  response()->json([ 
                    "status"=>0,
                    "code"=> 500,
                    "message"=>"Cource Id does not available in database!",
                    'data' => ['main_course_id'=>$id]
                   ]
                );

        }

         

    }

    public function deleteCourseDetail(Request $request,$cid,$scid)
    {

        $deleteCourse = Course::where('id',$scid)
        				->where('parent_id',$cid)->get();

       if($deleteCourse->count()){
        Course::where('id',$scid)
                        ->where('parent_id',$cid)->delete();
             return  response()->json([ 
                    "status"=>1,
                    "code"=> 200,
                    "message"=>"Sub Cource  deleted successfully",
                    'data' => ''
                   ]
                );
       } else {
             return  response()->json([ 
                    "status"=>0,
                    "code"=> 500,
                    "message"=>"main_course_id or sub_course_id does not matched",
                    'data' => ''
                   ]
                );
       } 

    }

    public function createBlog(Request $request, $id=null)
    {
        $blog = new Blogs;

        $table_cname = \Schema::getColumnListing('blogs');

         
        $validator = Validator::make($request->all(), [
            'blog_title' => 'required' 
        ]); 
        
       
        // Return Error Message
        if ($validator->fails()) {
                    $error_msg  =   [];
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }
                            
            return Response::json(array(
                'status' => 0,
                 'code' => 500,
                'message' => $error_msg[0],
                'data'  =>  []
                )
            );
        }



        $except = ['id','create_at','updated_at'];
        foreach ($table_cname as $key => $value) {
           
           if(in_array($value, $except )){
                continue;
           }

           $blog->$value = $request->get($value);
        }

        $blog->save();

       return  response()->json([ 
                    "status"=>1,
                    "code"=> 200,
                    "message"=>"Blog created successfully.",
                    'data' => $blog
                   ]
                );


    }
    public function updateBlog(Request $request, $id=null)
    {
        $blog = Blogs::find($id);

        if($blog==null)
        {
            return  response()->json([ 
                    "status"=>0,
                    "code"=> 500,
                    "message"=>"Blog id is invalid",
                    'data' => ['id'=>$id]
                   ]
                );
        }

        $table_cname = \Schema::getColumnListing('blogs');

        $except = ['id','create_at','updated_at'];

        $input = $request->all();

        foreach ($table_cname as $key => $value) {
           
           if(in_array($value, $except )){
                continue;
           }

           if(isset($input[$value])) {
               $blog->$value = $request->get($value); 
           } 
        }

        $blog->save();

       return  response()->json([ 
                    "status"=>1,
                    "code"=> 200,
                    "message"=>"Blog updated successfully.",
                    'data' => $blog
                   ]
                );


    }
    public function getBlog(Request $request){

        $blog_course_id= $request->get('blog_course_id');
        $id= $request->get('id');

        $blog = Blogs::with('courceDetail')
                ->where(function($query)use($blog_course_id,$id){
                    if($blog_course_id){
                          $query->where('blog_course_id',$blog_course_id);
                    }
                     if($id){
                          $query->where('id',$id);
                    }
                  
                })->get();

        return  response()->json([ 
                    "status"=>1,
                    "code"=> 200,
                    "message"=>"Blog list",
                    'data' => $blog
                   ]
                );

    }

    public function deleteBlog($id=null)
    {
        $blog = Blogs::where('id',$id)->delete();

        return  response()->json([ 
                    "status"=>1,
                    "code"=> 200,
                    "message"=>"Blog deleted successfully.",
                    'data' => []
                   ]
                );
    }
    
} 