import { readFileSync, writeFileSync } from "fs";
import { Glob } from "bun";

export function fixEnumTypes(content: string): string {
  return content.replace(
    /^(\s+)type: number(\n\s+enum:)/gm,
    "$1type: integer\n$1format: int32$2",
  );
}

if (import.meta.main) {
  const glob = new Glob("generated/oas/**/*.yaml");

  for await (const file of glob.scan(".")) {
    try {
      const content = readFileSync(file, "utf-8");
      const fixed = fixEnumTypes(content);

      if (fixed !== content) {
        writeFileSync(file, fixed, "utf-8");
        console.log(`Fixed numeric enum types in ${file}`);
      }
    } catch (err) {
      console.error(`Failed to process ${file}:`, err);
      process.exit(1);
    }
  }
}
