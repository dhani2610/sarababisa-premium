'use client';

import MasterDataView from '@/components/master/MasterDataView';

export default function MasterCabangPage() {
  return (
    <MasterDataView
      title="Master Cabang"
      itemLabel="Cabang"
      endpoint="/master/cabang"
    />
  );
}
