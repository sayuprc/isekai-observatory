import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Admin;

@doc("管理ユーザーID")
scalar adminUserId extends uuid;

@doc("管理ユーザー名")
scalar adminUserName extends string;

@doc("作成日時")
@encode(DateTimeKnownEncoding.rfc3339)
scalar createdAt extends utcDateTime;

@example(#{
  adminUserId: "0cc5f492-22fe-4f38-b702-8b2375bc0254",
  name: "テスト管理ユーザー",
  email: "example@example.com",
  createdAt: createdAt.fromISO("2019-12-09T10:30:20+09:00"),
  role: #{ name: "一般", value: RoleValue.General },
  permissions: #[#{ name: "管理ユーザー読み取り", value: PermissionValue.ReadAdminUser }],
})
model AdminUser {
  adminUserId: adminUserId;
  name: adminUserName;
  email: email;
  createdAt: createdAt;
  role: Role;
  permissions: Permission[];
}

@doc("役割名")
@minLength(1)
scalar roleName extends string;

@doc("役割の値")
@IsekaiObservatory.Shared.enumExtensions
enum RoleValue {
  @doc("特権")
  Privilege: 1,

  @doc("コンソール")
  Console: 2,

  @doc("一般")
  General: 3,
}

@example(#{ name: "特権", value: RoleValue.Privilege })
model Role {
  name: roleName;
  value: RoleValue;
}

@doc("権限名")
@minLength(1)
scalar permissionName extends string;

@doc("権限の値")
@IsekaiObservatory.Shared.enumExtensions
enum PermissionValue {
  @doc("管理ユーザー閲覧")
  ReadAdminUser: "read_admin_user",

  @doc("管理ユーザー編集")
  WriteAdminUser: "write_admin_user",

  @doc("人物閲覧")
  ReadPerson: "read_person",

  @doc("人物編集")
  WritePerson: "write_person",

  @doc("楽曲閲覧")
  ReadSong: "read_song",

  @doc("楽曲編集")
  WriteSong: "write_song",

  @doc("メディア閲覧")
  ReadMedia: "read_media",

  @doc("メディア編集")
  WriteMedia: "write_media",

  @doc("リリース閲覧")
  ReadRelease: "read_release",

  @doc("リリース編集")
  WriteRelease: "write_release",

  @doc("開催先閲覧")
  ReadVenue: "read_venue",

  @doc("開催先編集")
  WriteVenue: "write_venue",

  @doc("イベント閲覧")
  ReadEvent: "read_event",

  @doc("イベント編集")
  WriteEvent: "write_event",
}

@example(#{ name: "管理ユーザー読み取り", value: PermissionValue.ReadAdminUser })
model Permission {
  name: permissionName;
  value: PermissionValue;
}
