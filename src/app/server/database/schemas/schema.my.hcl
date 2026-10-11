variable "schema_name" {
  type = string
}

schema "db" {
  name    = var.schema_name
  charset = "utf8mb4"
  collate = "utf8mb4_bin"
}
