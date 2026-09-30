'use client';

import MasterDataView from '@/components/master/MasterDataView';

export default function MasterMerekPage() {
  return (
    <MasterDataView
      title="Master Merek"
      itemLabel="Merek"
      endpoint="/master/merek"
    />
  );
}
