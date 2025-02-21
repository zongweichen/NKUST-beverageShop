@extends('layout.main')

@section("content")

<article id="edit-product-admin-page" class="py-7">
    <div class="container">
        <div class="text-center section-title mb-5">
            <h2>會員修改</h2>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="px-4 py-3 text-darkred h5">
				
                    {{ csrf_field() }}
                    @if (session()->has('user_id'))
					<form id="editProfileForm" action="{{ route('editProfilePost') }}" method="post" enctype="multipart/form-data" class="p-3">
						@csrf
                            <div class="mb-3">
                                <div class="text-danger small" id="error-name"></div>
                                <label for="username" class="form-label">使用者名稱</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="{{ old('username', $user->name ?? '') }}" required />
                            </div>
                            <div class="mb-3">
                                <div class="text-danger small" id="error-email"></div>
                                <label for="email" class="form-label">電子郵件</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="{{ old('email', $user->email ?? '') }}" required />
                            </div>
                            <div class="mb-3">
                                <div class="text-danger small" id="error-phone_number"></div>
                                <label for="mobile_phone" class="form-label">行動電話</label>
                                <input type="tel" class="form-control" id="mobile_phone" name="mobile_phone" 
                                       value="{{ old('mobile_phone', $user->phone_number ?? '') }}" required />
                            </div>
                            
                            <div class="mb-3">
                                <div class="text-danger small" id="error-password"></div>
                                <label for="detailed_address" class="form-label">舊密碼</label>
                                <input type="password"class="form-control"id="password"name="password"required />
                            </div>
                            <div class="mb-3">
                                <div class="text-danger small" id="error-dpassword"></div>
                                <label for="detailed_address" class="form-label">新密碼</label>
                                <input type="password"class="form-control"id="password"name="dpassword"required />
                            </div>
							<button type="submit" class="btn btn-danger">送出</button>
							<button type="reset" class="btn btn-secondary">清除</button>
                        </form>
                    @else
                        <p>請先登入</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</article>
<script>
    document.getElementById("editProfileForm").addEventListener("submit", function(event) {
            event.preventDefault(); // 阻止表單提交，改用 AJAX

            let formData = new FormData(this);
            let email = formData.get("email");
            let mobilePhone = formData.get("mobile_phone");
            let emailPattern = /^[^@]+@[^@]+\.(com|tw|net|org)$/;
            let phonePattern = /^09\d{8}$/;
            let valid = true;

            document.querySelectorAll(".text-danger.small").forEach(el => el.innerHTML = "");
            // 驗證 Email
            if (!emailPattern.test(email)) {
                document.getElementById("error-email").innerHTML = "電子郵件格式錯誤，請包含 @ 及 .com！";
                valid = false;
            }
            // 驗證手機
            if (!phonePattern.test(mobilePhone)) {
                document.getElementById("error-phone_number").innerHTML = "手機號碼必須以 09 開頭，且為 10 位數字！";
                valid = false;
            }
            if (!valid) {
                return; // 若有錯誤，停止執行
            }

            fetch("{{ route('editProfilePost') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // 清空所有錯誤訊息
                document.querySelectorAll(".text-danger.small").forEach(el => el.innerHTML = "");

                if (data.errors) {
                    // 遍歷每個錯誤訊息，顯示到對應的欄位下方
                    for (let field in data.errors) {
                        let errorDiv = document.getElementById("error-" + field);
                        if (errorDiv) {
                            errorDiv.innerHTML = data.errors[field][0]; // 只顯示第一個錯誤訊息
                        }
                    }
                }else   if (data.success) {
                            alert(data.success);  // 顯示成功訊息
                            window.location.reload(); // ✅ 按「確認」後重新整理頁面
                        }
            })
            .catch(error => console.error("錯誤:", error));
        });
</script>



@endsection


