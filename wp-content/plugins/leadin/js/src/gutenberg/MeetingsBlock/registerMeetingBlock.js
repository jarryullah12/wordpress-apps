import React from 'react';
import { registerBlockType } from '@wordpress/blocks';
import CalendarIcon from '../Common/CalendarIcon';
import { connectionStatus } from '../../constants/leadinConfig';
import MeetingGutenbergPreview from './MeetingGutenbergPreview';
import MeetingSaveBlock from './MeetingSaveBlock';
import MeetingEdit from '../../shared/Meeting/MeetingEdit';
import ErrorHandler from '../../shared/Common/ErrorHandler';
import { __ } from '@wordpress/i18n';

const ConnectionStatus = {
  Connected: 'Connected',
  NotConnected: 'NotConnected',
};

export default function registerMeetingBlock() {
  const editComponent = props => {
    if (props.attributes.preview) {
      return <MeetingGutenbergPreview />;
    } else if (connectionStatus === ConnectionStatus.Connected) {
      return <MeetingEdit {...props} />;
    } else {
      return <ErrorHandler status={401} />;
    }
  };

  registerBlockType('leadin/hubspot-meeting-block', {
    title: __('Hubspot Meetings Scheduler', 'leadin'),
    description: __(
      'Schedule meetings faster and forget the back-and-forth emails. Your calendar stays full, and you stay productive',
      'leadin'
    ),
    icon: CalendarIcon,
    category: 'leadin-blocks',
    attributes: {
      url: {
        type: 'string',
        default: '',
      },
      preview: {
        type: 'boolean',
        default: false,
      },
    },
    example: {
      attributes: {
        preview: true,
      },
    },
    edit: editComponent,
    save: props => <MeetingSaveBlock {...props} />,
  });
}
