<?php

require_once 'SwatDB/SwatDBDataObject.php';
require_once 'Deliverance/DeliveranceCampaignFactory.php';
require_once 'Deliverance/dataobjects/DeliveranceCampaignSegment.php';

/**
 * @package   Deliverance
 * @copyright 2011-2012 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class DeliveranceNewsletter extends SwatDBDataObject
{
	// {{{ public properties

	/**
	 * @var integer
	 */
	public $id;

	/**
	 * @var string
	 */
	public $subject;

	/**
	 * @var string
	 */
	public $html_content;

	/**
	 * @var string
	 */
	public $text_content;

	/**
	 * @var string
	 */
	public $campaign_id;

	/**
	 * @var string
	 */
	public $campaign_report_url;

	/**
	 * @var SwatDate
	 */
	public $send_date;

	/**
	 * @var SwatDate
	 */
	public $createdate;

	// }}}
	// {{{ public function isSent()

	public function isSent()
	{
		$sent = false;

		if ($this->send_date instanceof SwatDate) {
			$send_date = clone $this->send_date;
			$send_date->toUTC();

			$now = new SwatDate();
			$now->toUTC();

			$sent = ($now->after($this->send_date));
		}

		return $sent;
	}

	// }}}
	// {{{ public function isScheduled()

	public function isScheduled()
	{
		return ($this->send_date instanceof SwatDate);
	}

	// }}}
	// {{{ public function getCampaign()

	public function getCampaign(SiteApplication $app)
	{
		// TODO: allow loading different types of campaigns based on segment.
		$campaign = DeliveranceCampaignFactory::get($app, 'default');

		$campaign->setId($this->getCampaignId());
		$campaign->setShortname($this->getCampaignShortname());
		$campaign->setSubject($this->subject);
		$campaign->setCampaignSegment($this->campaign_segment);
		$campaign->setHtmlContent($this->html_content);
		$campaign->setTextContent($this->text_content);
		$campaign->setTitle($this->getCampaignTitle());

		if ($this->send_date instanceof SwatDate) {
			$campaign->setSendDate($this->send_date);
		}

		return $campaign;
	}

	// }}}
	// {{{ protected function getCampaignId()

	protected function getCampaignId()
	{
		return $this->campaign_id;
	}

	// }}}
	// {{{ public function getCampaignStatus()

	public function getCampaignStatus(SiteApplication $app)
	{
		$status = null;

		if ($this->send_date === null) {
			$status = Deliverance::_('Draft');
		} else {
			if ($this->isSent()) {
				$status = Deliverance::_('Sent on: %s');
			} else {
				$status = Deliverance::_('Scheduled for: %s');
			}

			$date = clone $this->send_date;
			$date->convertTZ($app->default_time_zone);

			$status = sprintf($status,
				$date->formatLikeIntl(SwatDate::DF_DATE_TIME));
		}

		return $status;
	}

	// }}}
	// {{{ public function getCampaignShortname()

	public function getCampaignShortname()
	{
		if ($this->send_date === null) {
			$shortname = Deliverance::_('DRAFT');
		} else {
			$shortname = $this->send_date->formatLikeIntl('yyyy-MM-dd');
		}

		return $shortname;
	}

	// }}}
	// {{{ public function getCampaignTitle()

	public function getCampaignTitle()
	{
		$title = sprintf('%s: %s',
			$this->getCampaignShortname(),
			$this->subject);

		if ($this->campaign_segment != null) {
			$title.= ' - '.$this->campaign_segment->shortname;
		}

		return $title;
	}

	// }}}
	// {{{ protected function init()

	protected function init()
	{
		parent::init();

		$this->table = 'Newsletter';
		$this->id_field = 'integer:id';

		$this->registerInternalProperty('campaign_segment',
			SwatDBClassMap::get('DeliveranceCampaignSegment'));

		$this->registerDateProperty('send_date');
		$this->registerDateProperty('createdate');
	}

	// }}}
}

?>
