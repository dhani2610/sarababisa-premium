'use client';

import MasterDataView from '@/components/master/MasterDataView';

export default function MasterModelSeriPage() {
  return (
    <MasterDataView
      title="Master Model Seri"
      itemLabel="Model Seri"
      endpoint="/master/model-seri"
      hasMerekFilter={true}
    />
  );
}
