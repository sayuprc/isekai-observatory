import { createSignal } from 'solid-js';
import type { ReleaseFormatValue, ReleaseUpdateRequest } from '../../generated';
import { toMediaPayload, type MediumForm } from './MediaEditor';

/** フォームの初期値。作成ではコピー元があればその内容、なければ空 */
export interface ReleaseInitialValues {
  name: string;
  releasedOn: string;
  description: string;
  color: string;
  isDisplay: boolean;
  orderNo: number;
  formatValues: ReleaseFormatValue[];
  media: MediumForm[];
}

export const EMPTY_RELEASE_INITIAL_VALUES: ReleaseInitialValues = {
  name: '',
  releasedOn: '',
  description: '',
  color: '#989899',
  isDisplay: true,
  orderNo: 1,
  formatValues: [1],
  media: [{ name: '', tracks: [] }],
};

// 作成と編集で共通の入力状態
export const createReleaseForm = (initial: ReleaseInitialValues) => {
  const [name, setName] = createSignal(initial.name);
  const [releasedOn, setReleasedOn] = createSignal(initial.releasedOn);
  const [description, setDescription] = createSignal(initial.description);
  const [color, setColor] = createSignal(initial.color);
  const [isDisplay, setIsDisplay] = createSignal(initial.isDisplay);
  const [orderNo, setOrderNo] = createSignal(initial.orderNo);
  const [formatValues, setFormatValues] = createSignal<ReleaseFormatValue[]>([...initial.formatValues]);
  const [media, setMedia] = createSignal<MediumForm[]>(initial.media);

  const trackCount = () => media().reduce((total, medium) => total + medium.tracks.length, 0);

  const toRequestBody = (): ReleaseUpdateRequest => ({
    name: name().trim(),
    releasedOn: releasedOn(),
    description: description(),
    color: color(),
    isDisplay: isDisplay(),
    orderNo: orderNo(),
    formatValues: formatValues(),
    media: toMediaPayload(media()),
  });

  return {
    name,
    setName,
    releasedOn,
    setReleasedOn,
    description,
    setDescription,
    color,
    setColor,
    isDisplay,
    setIsDisplay,
    orderNo,
    setOrderNo,
    formatValues,
    setFormatValues,
    media,
    setMedia,
    trackCount,
    toRequestBody,
  };
};

export type ReleaseFormState = ReturnType<typeof createReleaseForm>;
