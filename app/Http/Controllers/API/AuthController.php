<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateMe;
use App\Mail\SignupMail;
use App\Models\User;
use App\Rules\ValidHCaptcha;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Info(version: '0.2.0', description: '[Project Home Page](https://ohmyshares.com)', title: 'OhMyShares API')]
#[OA\Server(url: L5_SWAGGER_CONST_HOST)]
#[OA\SecurityScheme(securityScheme: 'bearerAuth', type: 'http', name: 'bearerAuth', in: 'header', bearerFormat: 'JWT', scheme: 'bearer')]
#[OA\Tag(name: 'Auth', description: 'Auth & Profile')]
class AuthController extends Controller
{

    /**
     * Handle an authentication attempt.
     */
    #[OA\Post(
        path: '/auth',
        description: 'Auth',
        requestBody: new OA\RequestBody(
            description: 'Body payload',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/x-www-form-urlencoded',
                schema: new OA\Schema(
                    required: ['email', 'password'],
                    properties: [
                        new OA\Property(property: 'email', description: 'Email', type: 'string'),
                        new OA\Property(property: 'password', description: 'Password', type: 'string'),
                    ],
                    type: 'object'
                )
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 400, description: 'Request errors info'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function authenticate(Request $request): Response|array
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required",
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 400);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // Authentication passed...
            $user = Auth::user();
            $token = $user->generateApiToken();
            return ['api_token' => $token];
        }
        abort(401, 'Unauthorized');
    }


    /**
     * Register a new user.
     */
    #[OA\Post(
        path: '/signup',
        description: 'Signup',
        requestBody: new OA\RequestBody(
            description: 'Body payload',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/x-www-form-urlencoded',
                schema: new OA\Schema(
                    required: ['name', 'email', 'captcha'],
                    properties: [
                        new OA\Property(property: 'name', description: 'Name', type: 'string'),
                        new OA\Property(property: 'email', description: 'Email', type: 'string'),
                        new OA\Property(property: 'captcha', description: 'Captcha Response', type: 'string'),
                    ],
                    type: 'object'
                )
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 400, description: 'Request errors info'),
        ]
    )]
    public function signup(Request $request): Response|array
    {
        abort_unless(config('app.signup_enabled'), 404);

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users,email',
            'captcha' => ['required', new ValidHCaptcha()],
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 400);
        }

        $password = User::generatePassword();

        $user = new User([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => bcrypt($password),
            'locale' => App::getLocale(),
        ]);
        $saved = $user->save();
        $user->refresh();

        if ($saved) {
            Mail::send(new SignupMail($user, $password));
            return $user->meData();
        }

        abort(400, 'Signup error or bad request');
    }

    /**
     * Get user's profile info.
     */
    #[OA\Get(
        path: '/me',
        description: 'User`s profile',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function me(Request $request): array
    {
        /** @var User $user */
        $user = Auth::user();
        return Cache::remember('me_' . $user->id, 60, function () use ($user) {
            $user->touchLastActive();
            return $user->meData();
        });
    }

    /**
     * Update user's profile
     */
    #[OA\Patch(
        path: '/me',
        description: "Update user's profile",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            description: 'Body payload',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/x-www-form-urlencoded',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'name', description: 'Name', type: 'string'),
                        new OA\Property(property: 'is_ignore_funds_sufficiency', description: 'Ignore funds', type: 'boolean'),
                        new OA\Property(property: 'locale', description: "Locale string ('ru', 'en')", type: 'string'),
                    ],
                    type: 'object'
                )
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 400, description: 'Request errors info'),
        ]
    )]
    public function meUpdate(UpdateMe $request): array
    {
        $model = Auth::user();
        $model->update($request->all());
        Cache::forget('me_' . $model->id);
        return $model->meData();
    }

    /**
     * Change user's password
     */
    #[OA\Post(
        path: '/password-change',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            description: 'Body payload',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/x-www-form-urlencoded',
                schema: new OA\Schema(
                    required: ['password', 'password_new'],
                    properties: [
                        new OA\Property(property: 'password', description: 'Current password', type: 'string'),
                        new OA\Property(property: 'password_new', description: 'New password (min:6, max:100)', type: 'string'),
                    ],
                    type: 'object'
                )
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function passwordChange(Request $request): array
    {
        $request->validate([
            'password' => 'required|string',
            'password_new' => 'required|string|min:6|max:100',
        ]);
        $user = Auth::user();
        $credentials = [
            'email' => $user->email,
            'password' => $request->post('password'),
        ];

        if (Auth::guard('web')->attempt($credentials, false, false)) {
            // Authentication passed...
            $user->password = bcrypt($request->post('password_new'));
            $user->save();
            return [];
        }
        abort(400, __('Incorrect credentials'));
    }

    /**
     * Password reset request, send reset link to user's email
     */
    #[OA\Post(
        path: '/password-forgot',
        requestBody: new OA\RequestBody(
            description: 'Body payload',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/x-www-form-urlencoded',
                schema: new OA\Schema(
                    required: ['email'],
                    properties: [
                        new OA\Property(property: 'email', description: 'Email', type: 'string'),
                    ],
                    type: 'object'
                )
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function passwordForgot(Request $request): Response|array
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email|exists:users,email",
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 400);
        }

        $res = Password::sendResetLink($request->only('email'));

        if ($res != Password::RESET_LINK_SENT) {
            abort(400, __($res));
        }
        return [];
    }

    /**
     * Password reset by token
     */
    #[OA\Post(
        path: '/password-reset',
        requestBody: new OA\RequestBody(
            description: 'Body payload',
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/x-www-form-urlencoded',
                schema: new OA\Schema(
                    required: ['email'],
                    properties: [
                        new OA\Property(property: 'token', description: 'Token', type: 'string'),
                        new OA\Property(property: 'email', description: 'Email', type: 'string'),
                        new OA\Property(property: 'password', description: 'New password', type: 'string'),
                        new OA\Property(property: 'password_confirmation', description: 'New password (again)', type: 'string'),
                    ],
                    type: 'object'
                )
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function passwordReset(Request $request): array
    {
        $this->validate($request, [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);

        $credentials = $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );

        $res = Password::reset($credentials, function ($user, $password): void {
            $user->password = bcrypt($password);
            $user->save();
        });

        if ($res != Password::PASSWORD_RESET) {
            abort(400, __($res));
        }

        return [];
    }


    /**
     * Logout (invalidate token).
     */
    #[OA\Post(
        path: '/logout',
        description: 'Logout',
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 400, description: 'Request errors info'),
        ]
    )]
    public function logout(Request $request): array
    {
        $token = $request->bearerToken();
        $token_hash = hash('sha256', $token);
        Cache::forget($token_hash);
        return [];
    }
}
