<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User as UserModel;
use Illuminate\Support\Facades\Hash;

class User extends Controller
{
  const ListLimitMax = 2000;

  // Ilustrativo, eu deveria apenas chamar $sel->cursorPaginate($limit)
  //
  protected function doList(Request $request, $id) {
      $limitMax = static::ListLimitMax;
      $request->validate(['limit' => 'int|min:1|max:' . $limitMax]);
      $sel = UserModel::select('id', 'name', 'email');
      if (!is_null($id)) {
        $sel = $sel->where('id', '>', $id);
      }
      $sel->limit($request->input('limit', $limitMax));
      $users = $sel->get();
      $lastId = count($users) ? ['last-id' => $users[count($users) - 1]['id']] : [];
      return [
        ...$lastId,
        'data' => $users
      ];
  }

  public function list(Request $request, $id = null) {
      return response()->json($this->doList($request, $id));
  }

  public function listHorizontal(Request $request, $id = null) {
      $ret = $this->doList($request, $id);
      $data = ['id' => [], 'name' => [], 'email' => []];
      foreach ($ret['data'] as $user) {
          $data['id'][] = $user['id'];
          $data['name'][] = $user['name'];
          $data['email'][] = $user['email'];
      }
      $ret['data'] = $data;
      return response()->json($ret);
  }

  public function create(Request $request) {
      $request->validate([
          'name' => ['required', 'string', 'max:255'],
          'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
          'password' => ['required']
      ]);
      $user = UserModel::create([
          'name' => $request->name,
          'email' => $request->email,
          'password' => Hash::make($request->password),
      ]);
      return response()->json(['id' => $user->id]);
  }

  public function edit($id, Request $request) {
      if (!$request->hasAny(['name', 'email', 'password'])) {
          return response()->noContent();
      }

      $validatePassword = $request->has('password') ? ['password' => ['required']] : [];
      $request->validate([
          'name' => ['string', 'max:255'],
          'email' => ['string', 'email', 'max:255', 'unique:users'],
          ...$validatePassword
      ]);

      $user = UserModel::find($id);
      if (!$user) {
          abort(404);
      }

      if ($request->has('name')) {
          $user->name = $request->name;
      }
      if ($request->has('email')) {
          $user->email = $request->email;
      }
      if ($request->has('password')) {
          $user->password = Hash::make($request->password);
      }

      $user->save();

      return response()->noContent();
  }

  public function destroy($id) {
      $user = UserModel::withTrashed()->find($id);
      if (!$user) {
          abort(404);
      }
      if (!is_null($user->deleted_at)) {
          abort(410);
      }
      $user->delete();
      return response()->noContent();
  }
}
