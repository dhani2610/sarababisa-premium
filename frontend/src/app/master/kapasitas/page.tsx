'use client';

import MasterDataView from '@/components/master/MasterDataView';

export default function MasterKapasitasPage() {
  return (
    <MasterDataView
      title="Master Kapasitas"
      itemLabel="Kapasitas"
      endpoint="/master/kapasitas"
    />
  );
}
