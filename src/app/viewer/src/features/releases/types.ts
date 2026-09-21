import type { ReleaseFormat, ReleaseGroupListItem, ReleaseListItem, ReleaseMediumItem, ReleaseTrackItem } from '../../generated/types.gen.js';

export type ReleaseGroup = ReleaseGroupListItem;

export type Release = ReleaseListItem;

export type ReleaseMedium = ReleaseMediumItem;

export type ReleaseTrack = ReleaseTrackItem;

/** トラック詳細へのリンク可否。楽曲未紐づけまたは非表示のトラックはリンクしない */
export function isLinkableTrack(track: ReleaseTrack): track is ReleaseTrack & { songId: string } {
  return track.songId !== null && track.isDisplay;
}

/** 代表色: 公開リリースを発売日順に見て最初のもの */
export function representativeColor(releaseGroup: ReleaseGroup): string {
  return releaseGroup.releases[0].color;
}

/** グループ全体の提供形態(傘下リリースの和集合、値順) */
export function groupFormats(releaseGroup: ReleaseGroup): ReleaseFormat[] {
  const formatByValue = new Map<number, ReleaseFormat>();

  for (const release of releaseGroup.releases) {
    for (const format of release.formats) {
      formatByValue.set(format.value, format);
    }
  }

  return [...formatByValue.values()].toSorted((a, b) => a.value - b.value);
}
