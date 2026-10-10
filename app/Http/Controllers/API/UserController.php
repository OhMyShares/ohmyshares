<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\IndexUser;
use App\Http\Requests\User\StoreUser;
use App\Http\Requests\User\UpdateUser;
use App\Mail\SignupMail;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Users', description: 'User management endpoints')]
class UserController extends Controller
{
    /**
     * Display a listing of users.
     *
     * @return \Illuminate\Http\Response
     */
    #[OA\Get(
        path: '/users',
        description: 'Get list of users',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'name', type: 'string'),
                            new OA\Property(property: 'email', type: 'string'),
                            new OA\Property(property: 'role', type: 'integer'),
                            new OA\Property(property: 'locale', type: 'string'),
                            new OA\Property(property: 'is_test', type: 'boolean'),
                            new OA\Property(property: 'is_ignore_funds_sufficiency', type: 'boolean'),
                            new OA\Property(property: 'created_at', type: 'string', format: 'datetime'),
                            new OA\Property(property: 'updated_at', type: 'string', format: 'datetime'),
                        ]
                    )
                )
            ),
        ]
    )]
    public function index(IndexUser $request): Collection
    {
        return User::all()->makeVisible('role');
    }

    /**
     * Store a newly created user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    #[OA\Post(
        path: '/users',
        description: 'Create new user',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string'),
                    new OA\Property(property: 'password', description: 'Generated and emailed to the user when omitted', type: 'string'),
                    new OA\Property(property: 'role', type: 'integer'),
                    new OA\Property(property: 'locale', type: 'string'),
                    new OA\Property(property: 'is_test', type: 'boolean'),
                    new OA\Property(property: 'is_ignore_funds_sufficiency', type: 'boolean'),
                ]
            )
        ),
        tags: ['Users'],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation errors'),
            new OA\Response(response: 201, description: 'Created'),
        ]
    )]
    public function store(StoreUser $request): JsonResponse
    {
        $data = $request->validated();
        $password = $data['password'] ?? null;
        $isPasswordGenerated = $password === null;
        $data['password'] = $password ?? User::generatePassword();
        $data['locale'] ??= App::getLocale();

        $user = $this->fillUser(new User(), $data);
        $user->save();

        if ($isPasswordGenerated) {
            Mail::send(new SignupMail($user, $data['password']));
        }

        return response()->json($user->refresh()->makeVisible('role'), 201);
    }

    /**
     * Display the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    #[OA\Get(
        path: '/users/{id}',
        description: 'Get user details',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'User not found'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function show(User $user): User
    {
        return $user->makeVisible('role');
    }

    /**
     * Update the specified user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    #[OA\Put(
        path: '/users/{id}',
        description: 'Update user details',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string'),
                    new OA\Property(property: 'password', description: 'Kept unchanged when empty', type: 'string'),
                    new OA\Property(property: 'role', type: 'integer'),
                    new OA\Property(property: 'locale', type: 'string'),
                    new OA\Property(property: 'is_test', type: 'boolean'),
                    new OA\Property(property: 'is_ignore_funds_sufficiency', type: 'boolean'),
                ]
            )
        ),
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'User not found'),
            new OA\Response(response: 422, description: 'Validation errors'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function update(UpdateUser $request, User $user): User
    {
        $this->fillUser($user, $request->validated())->save();
        Cache::forget('me_' . $user->id);

        return $user->makeVisible('role');
    }

    /**
     * Remove the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    #[OA\Delete(
        path: '/users/{id}',
        description: 'Delete user',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'User not found'),
            new OA\Response(response: 422, description: 'Admin cannot delete himself'),
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function destroy(User $user): JsonResponse
    {
        abort_if($user->is(Auth::user()), 422, 'You cannot delete your own account');

        $user->delete();
        Cache::forget('me_' . $user->id);

        return response()->json(['message' => 'User deleted successfully']);
    }

    private function fillUser(User $user, array $data): User
    {
        $data = array_filter($data, fn ($value) => $value !== null);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->fill(Arr::except($data, ['role', 'is_test']));

        if (isset($data['role'])) {
            $user->role = $data['role'];
        }
        if (isset($data['is_test'])) {
            $user->is_test = $data['is_test'];
        }

        return $user;
    }
} 