const isControlCharacter = (character: string): boolean => {
  const codePoint = character.codePointAt(0) ?? 0;
  return codePoint <= 0x1f || codePoint === 0x7f;
};

export const validateVenueName = (name: string): string | undefined => {
  const normalizedName = name.trim().normalize('NFC');

  if (normalizedName.length === 0) {
    return '開催先名を入力してください';
  }

  if (normalizedName.length > 255) {
    return '開催先名は255文字以内で入力してください';
  }

  if ([...name].some(isControlCharacter)) {
    return '開催先名に改行や制御文字は含められません';
  }

  return undefined;
};
