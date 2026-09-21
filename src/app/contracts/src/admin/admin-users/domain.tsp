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
enum RoleValue {
  Privilege: 1,
  Console: 2,
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
enum PermissionValue {
  ReadAdminUser: "read_admin_user",
  WriteAdminUser: "write_admin_user",
  ReadPerson: "read_person",
  WritePerson: "write_person",
  ReadSong: "read_song",
  WriteSong: "write_song",
  ReadMedia: "read_media",
  WriteMedia: "write_media",
  ReadRelease: "read_release",
  WriteRelease: "write_release",
  ReadVenue: "read_venue",
  WriteVenue: "write_venue",
}

@example(#{ name: "管理ユーザー読み取り", value: PermissionValue.ReadAdminUser })
model Permission {
  name: permissionName;
  value: PermissionValue;
}
