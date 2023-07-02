import React from 'react';
import HubspotWrapper from '../Common/HubspotWrapper';
import { pluginPath } from '../../constants/leadinConfig';
import AsyncSelect from '../Common/AsyncSelect';
import { __ } from '@wordpress/i18n';

export default function FormSelector({ loadOptions, onChange, value }) {
  return (
    <HubspotWrapper pluginPath={pluginPath}>
      <p data-test-id="leadin-form-select">
        <b>
          {__(
            'Select an existing form or create a new one from a template',
            'leadin'
          )}
        </b>
      </p>
      <AsyncSelect
        placeholder={__('Search for a form', 'leadin')}
        value={value}
        loadOptions={loadOptions}
        onChange={onChange}
      />
    </HubspotWrapper>
  );
}
