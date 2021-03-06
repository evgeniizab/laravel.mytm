<?php

namespace App\Http\Controllers\Api\v1;

use App\Exceptions\Filter;
use App\Exceptions\UserIsAlreadyExists;
use App\Exceptions\UserNotSignUp;
use App\Filters\ProjectFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\SignInRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Requests\SignUpRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection as AnonymousResourceCollectionAlias;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserController
 * @package App\Http\Controllers\Api\v1
 */
class UserController extends Controller
{
    const HTTP_CREATED = Response::HTTP_CREATED;
    const HTTP_OK = Response::HTTP_OK;
    const HTTP_UNAUTHORIZED = Response::HTTP_UNAUTHORIZED;

    /**
     * Регистрация нового пользователя
     *
     * (post) /singup
     *
     * @param SignUpRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws UserIsAlreadyExists
     * @throws UserNotSignUp
     */
    public function signUp(SignUpRequest $request)
    {
        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $user = User::where('email', request('email'))->first();

        if ($user) {
            throw new UserIsAlreadyExists();
        }

        try {
            $user = User::create($data);
        } catch (QueryException $exception) {
            throw new UserNotSignUp();
        }

        $success['name'] =  $user->name;
        $success['token'] = $this->getUserToken($user, "MyToken");
        $response =  self::HTTP_CREATED;

        return $this->getResponse("success", $success, $response, $user->id);
    }

    /**
     * Авторизация пользователя
     *
     * (post) /signin
     *
     * @param SignInRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signIn(SignInRequest $request)
    {
        $data = $request->all();
        $credentials = [
            'email' => request('email'),
            'password' => request('password'),
        ];

        if (auth()->attempt($credentials)) {
            $user = Auth::user();
            $token['token'] = $this->getUserToken($user, "MyToken");
            $response = self::HTTP_OK;

            return $this->getResponse("authorized", $token, $response, $user->id);
        } else {
            $error = "Unauthorized Access";
            $response = self::HTTP_UNAUTHORIZED;

            return $this->getResponse("error", $error, $response);
        }
    }

    /**
     * Выход из системы
     *
     * (get) /signout
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws UserNotSignUp
     */
    public function signOut(Request $request)
    {
        $token = $request->user()->token();

        if (isset($token)) {
            $token->revoke();
        }

        return response()->json([
                'data' => [
                    'data' => [
                        'message' => 'You are successfully signout'
                    ],
                    'links' => [
                        'self' => route('users.signout'),
                    ]
                ]
        ]);
    }

    /**
     * Создаем токен
     *
     * @param $user
     * @param string|null $token_name
     * @return string
     */
    public function getUserToken($user, string $token_name = null)
    {

        if (isset($user)) {
            return $user->createToken($token_name)->accessToken;
        } else {
            return '';
        }
    }

    /**
     *
     * Отправлем ответ
     *
     * @param string|null $status
     * @param null $data
     * @param $response
     * @param null $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getResponse(string $status = null, $data = null, $response, $id = null)
    {
        if (isset($id)) {
            $data = [
                'data' => [
                    'type' => 'user',
                    'user_id' => $id,
                    'status' => $status,
                    'attributes' => $data,
                ],
                'links' => [
                    'self' => route('users.show', $id),
                ]];
        } else {
            $data = [
                'data' => [
                    'type' => 'user',
                    'status' => $status,
                    'attributes' => $data,
                ]
            ];
        }

        return response()->json($data, $response);
    }

    /**
     * @return UserResource
     */
    public function info()
    {
        $user = auth()->user();
        return new UserResource($user);
    }

    /**
     * Информация по всем пользователям
     *
     * (get) /users/
     *
     * @param ProjectFilter $filters
     * @return AnonymousResourceCollectionAlias
     * @throws Filter
     */
    public function index(ProjectFilter $filters)
    {
        $paginate = $filters->getPaginate();

        try {
            $users = User::filter($filters)->paginate($paginate);
        } catch (QueryException $exception) {
            throw new Filter();
        }

        return UserResource::collection($users);
    }

    /**
     * @param $id
     * @return UserResource
     */
    public function show($id)
    {
        $user = User::findOrfail($id);
        return new UserResource($user);
    }

    /**
     * @return UserResource
     */
    public function infoUser()
    {
        $user = auth()->user();
        return new UserResource($user);
    }

    /**
     * @param UserUpdateRequest $request
     * @param User $user
     * @return UserResource|\Illuminate\Http\JsonResponse
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        if (!$user) {
            $error = 'Пользователя с таким id не существует';
        }

        if ($user->id != auth()->user()->id) {
            $error = 'У вас нет прав чтобе изменить информацию о пользователе.';
        }

        if (isset($error)) {
            return response()->json(['data' => ['error' => $error]], 400);
        } else {
            request('name') && $user->name = request('name');
            request('email') && $user->email = request('email');
            $password = request('password');
            $old_password = request('old_password');

            if (Hash::check($old_password, auth()->User()->password)) {
                $user->update(["password" => Hash::make($password)]);
            }

            $user->save;

            return new UserResource($user);
        }
    }

    /**
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(User $user)
    {
        if ($user->id != auth()->user()->id) {
            $error = 'У вас нет прав чтобы удалить пользователя.';
        }

        if (isset($error)) {
            return response()->json(['data' => ['error' => $error]], 400);
        } else {
            $user->delete();
            return response()->json([
                'data' => [
                'message' => 'Пользователь удален'
                ],
                'links' => [
                'self' => route('users.index'),
                ]

            ], 200);
        }
    }
}
