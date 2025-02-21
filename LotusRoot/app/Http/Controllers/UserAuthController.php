<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth; 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Hash;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Socialite;


class UserAuthController extends Controller
{


    // 註冊邏輯
    public function SignUpProcess(Request $request)
    {
       // 獲取所有資料
        $input = $request->all();

         //客製化錯誤訊息
        $messages = [
            "name.required" => "請輸入姓名",
            "email.required" => "請輸入電子郵件",
            "email.email" => "請輸入有效的電子郵件",
            "email.unique" => "此電子郵件已被註冊",
            "phone_number.required" => "請輸入手機號碼",
            "phone_number.regex" => "請輸入有效的手機號碼",
            "password.required" => "請輸入密碼",
            "password.min" => "密碼至少要有6個字元",
            "password.confirmed" => "密碼與確認密碼不一致",
            "password.regex" => "密碼至少要有一個大寫字母"
        ];

       // 驗證資料
        $validator = Validator::make($input, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|regex:/^[0-9]{10}$/',
            'password' => 'required|min:6|confirmed|regex:/[A-Z]/', // 確保 password 和 confirm_password 一致
        ], $messages);


       // 驗證失敗時，返回錯誤訊息
        if ($validator->fails()) {
            $errors = $validator->errors();
            $customErrors = [];

            foreach ($errors->messages() as $field =>$messages) {
                $customErrors[$field] = implode(",", $messages);
            };

            return response() ->json([
                "success" => false,
                "errors" => $customErrors
            ], 400);




            
        }

        // 密碼加密
        $input['password'] = Hash::make($input['password']);

        try {
            // 儲存使用者資料
            User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'phone_number' => $input['phone_number'],
                'password' => $input['password'],
            ]);

            return response()->json([
                'success' => true,
                'message' => '註冊成功'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '註冊發生錯誤，請稍後再試。',
                'errors' => $e->getMessage()
           ], 500);  // 500 是伺服器錯誤代碼
        }
    }
     // 登入邏輯
    public function SignInProcess(Request $request)
    {
        // 取得 email 和 password
        $input = $request->only('email', 'password');

        // 檢查 email 和 password 是否為空
        if (empty($input['email']) || empty($input['password'])) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'email' => empty($input['email']) ? '請輸入有效的電子郵件' : null,
                    'password' => empty($input['password']) ? '請輸入密碼' : null
                ]
            ], 400);
        }

        // 透過 email 查詢使用者
        $tmpuser = User::where('email', $input['email'])->first();

        // 如果查無帳號，回傳錯誤訊息
        if (is_null($tmpuser)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'email' => '查無此帳號'
                ]
            ], 400);
        }

        // 驗證密碼是否正確
        if (!Hash::check($input['password'], $tmpuser->password)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'password' => '密碼錯誤'
                ]
            ], 400);
        }

        session([
            'user_id' => $tmpuser->id,
            'user_type' => $tmpuser->type, // 'G' = 一般使用者, 'A' = 管理員
        ]);

       

        // 判斷使用者類型，管理者 (A) 跳轉 /user/auth/test，會員 (G) 跳轉 /
        $redirect_url = ($tmpuser->type === 'A') ? '/user/auth/editProfileGet' : '/';

        // 回傳登入成功及對應的跳轉網址
        return response()->json([
            'success' => true,
            'message' => '登入成功',
            'redirect_url' => $redirect_url
        ]);
    }
    // 清除 session 中的 user_id
    public function SignOut()
    {
        session()->forget('user_id');  
        return redirect('/');  // 登出後重定向回登入頁面
    }
    // 測試管理員導入
    public function Signtest() {
        return view('layout.main'); 
    }
    // 會員變更頁面
    public function editProfileGet()
    {
        $userId = Session::get('user_id'); // 從 session 取得 user_id

        if (!$userId) {
            return redirect('/')->with('error', '請先登入');
        }

        $user = User::find($userId); // 用 user_id 查詢使用者資料

        if (!$user) {
            return redirect('/')->with('error', '找不到該使用者');
        }

        return view('layout.member', compact('user')); // 傳遞 $user 給 Blade
    }
    // 會員變更邏輯
    public function editProfilePost(Request $request)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return response()->json(['errors' => ['general' => ['用戶不存在！']]], 404);
        }

        // 驗證舊密碼是否正確
        if (!Hash::check($request->input('password'), $user->password)) {
            return response()->json(['errors' => ['password' => ['舊密碼不正確！']]], 400);
        }

        // 驗證輸入欄位 加規則限制
        $validator = Validator::make($request->all(), [
            'username'   => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255'],
            'mobile_phone' => ['required', 'string', 'max:15'],
            'dpassword'  => ['nullable', 'string', 'min:6', 'max:10', 'regex:/^[A-Z]/'],
            'email' => ['required', 'email', 'regex:/^[^@]+@[^@]+\.(com|tw|net|org)$/'],
            'mobile_phone' => ['required', 'regex:/^09\d{8}$/'],
        ], [
            'dpassword.regex' => '新密碼必須以大寫字母開頭！',
            'dpassword.min' => '新密碼至少需要 6 個字元！',
            'dpassword.max' => '新密碼最多只能 10 個字元！',
            'email.regex' => '電子郵件格式錯誤，請包含 @ 及 .com！',
            'mobile_phone.regex' => '手機號碼必須以 09 開頭，且為 10 位數字！',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // 更新用戶資料
        $user->name = $request->input('username');
        $user->email = $request->input('email');
        $user->phone_number = $request->input('mobile_phone');

        // 若有提供新密碼，則更新
        if ($request->filled('dpassword')) {
            $user->password = Hash::make($request->input('dpassword'));
        }

        $user->save();

        return response()->json(['success' => '更新成功！']);
    }
 
}