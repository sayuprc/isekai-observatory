type EnumSchema = {
  'enum'?: (string | number)[];
  'x-enum-descriptions'?: string[];
};

// SongTypeValue → SONG_TYPE_NAMES
export const toConstantName = (schemaName: string): string =>
  `${schemaName
    .replace(/Value$/, '')
    .replace(/([a-z0-9])([A-Z])/g, '$1_$2')
    .toUpperCase()}_NAMES`;

// 契約の enum メンバーの @doc (x-enum-descriptions) を表示名として、値から表示名への対応表を作る
export const renderEnumNames = (schemas: Record<string, EnumSchema>): string => {
  // ドットを含む名前は hey-api が型名を組み替えるため、型を import できず対象にしない
  const entries = Object.entries(schemas)
    .filter(([name, schema]) => schema['x-enum-descriptions'] !== undefined && /^[A-Za-z]\w*$/.test(name))
    .toSorted(([a], [b]) => a.localeCompare(b));

  const tables = entries.map(([name, schema]) => {
    const values = schema.enum ?? [];
    const descriptions = schema['x-enum-descriptions'] ?? [];

    if (values.length !== descriptions.length) {
      throw new Error(`${name} の enum と x-enum-descriptions の件数が一致しません`);
    }

    const lines = values.map(
      (value, index) => `  ${JSON.stringify(String(value))}: ${JSON.stringify(descriptions[index])},`,
    );

    return `export const ${toConstantName(name)}: Record<${name}, string> = {\n${lines.join('\n')}\n};\n`;
  });

  return [
    '// このファイルは scripts/generate-enum-names.ts が契約から生成する。直接編集しない',
    `import type { ${entries.map(([name]) => name).join(', ')} } from './types.gen';`,
    '',
    tables.join('\n'),
  ].join('\n');
};

if (import.meta.main) {
  const [input, output] = Bun.argv.slice(2);

  if (!input || !output) {
    throw new Error('usage: bun run scripts/generate-enum-names.ts <oas.yaml> <output.ts>');
  }

  const spec = Bun.YAML.parse(await Bun.file(input).text()) as { components: { schemas: Record<string, EnumSchema> } };

  await Bun.write(output, renderEnumNames(spec.components.schemas));
}
