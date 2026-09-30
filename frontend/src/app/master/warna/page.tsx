'use client';

import MasterDataView from '@/components/master/MasterDataView';

export default function MasterWarnaPage() {
  return (
    <MasterDataView
      title="Master Warna"
      itemLabel="Warna"
      endpoint="/master/warna"
    />
  );
}
