<?php
/**
 * @package         LangMan4Dev
 * @subpackage      com_lang4dev
 * @author          Thomas Finnern <InsideTheMachine.de>
 * @copyright  (c)  2022-2025 Lang4dev Team
 * @license         GNU General Public License version 2 or later
 */

namespace Finnern\Component\Lang4dev\Administrator\Helper;

use Joomla\CMS\Factory;

use function defined;

// no direct access
defined('_JEXEC') or die;

/**
 * Keeps one translation (line) of one language item
 * with above comments, behind comments
 * ? empty lines ?
 *
 * prepared items have a twin in the main language and
 * no content (content may exist on later code development)
 *
 * @package Lang4dev
 *
 * @since   __BUMP_VERSION__
 */
class langTranslation
{
    /**
     * @var mixed|string
     * @since version
     */
    public $transId = '';
    public $translationText = '';
    public $commentsBefore = [];
    public $commentBehind = '';
    public $lineNr = -1;
    public $isPrepared = false;

    /**
     * @since __BUMP_VERSION__
     */
    public function __construct(
        $transId = '',
        $translationText = '',
        $commentsBefore = [],
        $commentBehind = '',
        $lineNr = -1,
        $isPrepared = false
    ) {
        $this->transId         = $transId;
        $this->translationText = $translationText;
        $this->commentsBefore  = $commentsBefore;
        $this->commentBehind   = $commentBehind;
        $this->lineNr          = $lineNr;
        $this->isPrepared      = $isPrepared;
    }

    /**
     * remove all entries but keep line index
     *
     * @since version
     */
    public function clean()
    {
        $this->init($this->lineNr);
    }

    /**
     * @param $lineNr
     *
     *
     * @since version
     */
    public function init($lineNr = -1)
    {
        $this->transId         = '';
        $this->translationText = '';
        $this->commentsBefore  = [];
        $this->commentBehind   = '';
        $this->lineNr          = $lineNr;
        $this->isPrepared      = false;
    }

    public function __toTextLine()
    {
        $lines = [];

        //--- translation line -------------------------------

        $transLine = $this->transId . '=' . $this->translationText;

        if (strlen($this->commentBehind) > 0) {
            $transLine .= ' //' . $this->commentBehind;
        }
        $lines [] = '"' . $transLine . '"';

        //--- comments before -------------------------------

        if ($this->commentsBefore) {
            $lines [] = 'commentsBefore [] = "' . count($this->commentsBefore) . '"';
        }

        //---  -------------------------------

        $lines [] = 'lineNr = "' . $this->lineNr . '"';
        $lines [] = 'isPrepared = "' . ($this->isPrepared ? 'true' : 'false') . '"';

        return implode(', ', $lines);
    }

    public function __toText()
    {
        $lines = [];

        //--- comments before -------------------------------

        foreach ($this->commentsBefore as $commentLine) {
            $lines [] = $commentLine;
        }

        //--- translation line -------------------------------

        $transLine = $this->transId . '=' . $this->translationText;

        if (strlen($this->commentBehind) > 0) {
            $transLine .= ' //' . $this->commentBehind;
        }
        $lines [] = '"' . $transLine . '"';

        $lines [] = '$lineNr = "' . $this->lineNr . '"';
        $lines [] = '$isPrepared = "' . ($this->isPrepared ? 'true' : 'false') . '"';

        return $lines;
    }

} // class