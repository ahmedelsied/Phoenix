<?php

namespace App\Http\Controllers\Auth;

use App\Events\Registered;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\auth\LoggedInUserResource;
use App\Repositories\Contracts\ICode;
use App\Repositories\Contracts\IUser;
use App\Traits\HttpResponse;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    use HttpResponse;

    protected $user, $code;

    public function __construct(IUser $user, Icode $code) {
        $this->user = $user;
        $this->code = $code;
    }

    /**
     * @OA\Post(
     * path="/api/signup/{from}",
     * operationId="Register",
     * tags={"Authentication"},
     * summary="User signup",
     * description="User signup here",
     *   @OA\Parameter(
     *         name="from",
     *         in="path",
     *         description="web or flutter",
     *         required=true,
     *      ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"name","email", "password", "password_confirmation"},
     *               @OA\Property(property="name", type="text"),
     *               @OA\Property(property="email", type="email"),
     *               @OA\Property(property="password", type="password"),
     *               @OA\Property(property="password_confirmation", type="password")
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Register Successfully / user added to database",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     * )
     */
    public function register(RegisterRequest $request, $from): \Illuminate\Http\Response
    {
        $data = array_merge($request->all(), ['password' => bcrypt($request->password)]);
        $user = $this->user->create($data);
        $user->markEmailAsVerified();
        $token = Auth::guard('user')->attempt($request->all());
        $user['token'] = $token;
        // event(new Registered($user, $from, $this->code));
        return self::returnData('user', new LoggedInUserResource($user),'logged in successfully',200);
    }
}
