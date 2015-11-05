<?php
namespace UserAgentParser;

use UserAgentParser\Provider\AbstractProvider;
use UserAgentParser\Provider\Chain;

class GetMatrix
{
    private $provider;

    private $userAgents = [];

    private $statistics = [];

    public function setProvider(AbstractProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     *
     * @return AbstractProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    public function setUserAgents(array $userAgents)
    {
        $this->userAgents = $userAgents;
    }

    public function getUserAgents()
    {
        return $this->userAgents;
    }

    private function getIconQuestionIfEmpty($input)
    {
        if ($input == '') {
            return '<i class="glyphicon glyphicon-question-sign"></i>';
        }
        
        return $input;
    }

    private function getIcon($bool)
    {
        if ($bool === false) {
            return '<i class="glyphicon glyphicon-remove"></i>';
        }
        
        if ($bool === true) {
            return '<i class="glyphicon glyphicon-ok"></i>';
        }
        
        return '<i class="glyphicon glyphicon-question-sign"></i>';
    }

    private function getTdDetailResult(array $result)
    {
        $str = '';
        
        /*
         * Browser
         */
        $str .= '<tr>';
        $str .= '<td style="white-space: nowrap;">Browser</td>';
        
        $str .= '<td style="white-space: nowrap;">';
        $str .= $this->getIconQuestionIfEmpty($result['browser']['family']) . ' | ' . $this->getIconQuestionIfEmpty($result['browser']['version']);
        $str .= '</td>';
        $str .= '</tr>';
        
        /*
         * OS
         */
        $str .= '<tr>';
        $str .= '<td style="white-space: nowrap;">OS</td>';
        
        $str .= '<td style="white-space: nowrap;">';
        $str .= $this->getIconQuestionIfEmpty($result['operatingSystem']['family']) . ' | ' . $this->getIconQuestionIfEmpty($result['operatingSystem']['version']) . ' | ' . $this->getIconQuestionIfEmpty($result['operatingSystem']['platform']);
        $str .= '</td>';
        $str .= '</tr>';
        
        /*
         * Device
         */
        $str .= '<tr>';
        $str .= '<td style="white-space: nowrap;">Device brand</td>';
        $str .= '<td style="white-space: nowrap;">';
        $str .= $this->getIconQuestionIfEmpty($result['device']['brand']);
        $str .= '</td>';
        $str .= '</tr>';
        
        $str .= '<tr>';
        $str .= '<td style="white-space: nowrap;">Device model</td>';
        $str .= '<td style="white-space: nowrap;">';
        $str .= $this->getIconQuestionIfEmpty($result['device']['model']);
        $str .= '</td>';
        $str .= '</tr>';
        
        $str .= '<tr>';
        $str .= '<td style="white-space: nowrap;">Device type</td>';
        $str .= '<td style="white-space: nowrap;">';
        $str .= $this->getIconQuestionIfEmpty($result['device']['type']);
        $str .= '</td>';
        $str .= '</tr>';
        
        $str .= '<tr>';
        $str .= '<td style="white-space: nowrap;">Is mobile</td>';
        $str .= '<td style="white-space: nowrap;">';
        $str .= $this->getIcon($result['device']['isMobile']);
        $str .= '</td>';
        $str .= '</tr>';
        
        /*
         * bot
         */
        $str .= '<tr>';
        $str .= '<td style="white-space: nowrap;">Is bot</td>';
        $str .= '<td style="white-space: nowrap;">';
        $str .= $this->getIcon($result['bot']['isBot']);
        $str .= '</td>';
        $str .= '</tr>';
        
        $str .= '<tr>';
        $str .= '<td style="white-space: nowrap;">Bot name</td>';
        $str .= '<td style="white-space: nowrap;">';
        $str .= $this->getIconQuestionIfEmpty($result['bot']['name']);
        $str .= '</td>';
        $str .= '</tr>';
        
        $str .= '<tr>';
        $str .= '<td style="white-space: nowrap;">Bot category</td>';
        $str .= '<td style="white-space: nowrap;">';
        $str .= $this->getIconQuestionIfEmpty($result['bot']['type']);
        $str .= '</td>';
        $str .= '</tr>';
        
        return $str;
    }

    private function getTdResult(array $result)
    {
        $str = '<table class="table table-condensed">';
        
        if ($this->hasResult($result)) {
            $str .= $this->getTdDetailResult($result);
        } else {
            $str .= 'no result';
        }
        
        /*
         * Meta
         */
        $str .= '<tr>';
        $str .= '<td style="white-space: nowrap;">parseTime</td>';
        $str .= '<td style="white-space: nowrap;">';
        $str .= '<i class="glyphicon glyphicon-time"></i> ' . round($result['parseTime'], 5);
        $str .= '</td>';
        $str .= '</tr>';
        
        /*
         * Raw
         */
        $str .= '<tr>';
        $str .= '<td>Raw</td>';
        
        $str .= '<td>';
        $str .= '<button class="btn btn-default" onclick="$(this).parent().children(\'div\').toggleClass(\'hidden\');">toggle</button>';
        $str .= '<div class="hidden">';
        $str .= '<pre>' . print_r($result, true) . '</pre>';
        $str .= '</div>';
        
        $str .= '</td>';
        $str .= '</tr>';
        
        $str .= '</table>';
        
        return $str;
    }

    private function hasResult(array $result)
    {
        if ($result['browser']['family'] != '' || $result['operatingSystem']['family'] != '' || $result['device']['type'] != '' || $result['bot']['isBot'] != '') {
            return true;
        }
        
        return false;
    }

    private function addStatistics(array $result)
    {
        if (! isset($this->statistics[$result['provider']])) {
            $this->statistics[$result['provider']] = [
                
                'parseTime' => 0,
                
                'resultsFound' => 0,
                
                'browser' => [
                    'family' => 0,
                    'version' => 0
                ],
                
                'operatingSystem' => [
                    'family' => 0,
                    'version' => 0,
                    'platform' => 0
                ],
                
                'device' => [
                    'brand' => 0,
                    'model' => 0,
                    'type' => 0,
                    
                    'isMobile' => 0
                ],
                
                'bot' => [
                    'isBot' => 0,
                    
                    'name' => 0,
                    'type' => 0
                ]
            ];
        }
        
        $this->statistics[$result['provider']]['parseTime'] += $result['parseTime'];
        
        /*
         * No result?
         */
        if (! $this->hasResult($result)) {
            return;
        }
        
        $this->statistics[$result['provider']]['resultsFound'] += 1;
        
        $this->addCountDetail($result, 'browser');
        $this->addCountDetail($result, 'operatingSystem');
        $this->addCountDetail($result, 'device');
        $this->addCountDetail($result, 'bot');
    }

    private function addCountDetail(array $result, $partType = 'browser')
    {
        $currentResult = $this->statistics[$result['provider']][$partType];
        
        $resultPart = $result[$partType];
        foreach ($resultPart as $key => $part) {
            if ($part !== null) {
                $currentResult[$key] += 1;
            }
        }
        
        $this->statistics[$result['provider']][$partType] = $currentResult;
    }

    public function toHtml()
    {
        $table = '<table class="table table-bordered table-condensed">';
        
        $table .= '<tr>';
        $table .= '<th>UA</th>';
        if ($this->getProvider() instanceof Chain) {
            foreach ($this->getProvider()->getProviders() as $provider) {
                $table .= '<th>' . $provider->getName() . '</th>';
            }
        } else {
            $table .= '<th>' . $this->getProvider()->getName() . '</th>';
        }
        
        $table .= '</tr>';
        
        $sum = count($this->getUserAgents());
        
        $tableRows = '';
        
        $i = 1;
        foreach ($this->getUserAgents() as $userAgent) {
            $tableRows .= '<tr>';
            $tableRows .= '<td>' . $userAgent . '</td>';
            
            foreach ($this->getProvider()->parse($userAgent) as $result) {
                $this->addStatistics($result);
                
                $tableRows .= '<td>' . $this->getTdResult($result) . '</td>';
            }
            
            $tableRows .= '</tr>';
            
            echo $i . '/' . $sum . PHP_EOL;
            
            $i ++;
        }
        
        $table .= '<tr>';
        $table .= '<td><strong>Summary</strong></td>';
        foreach ($this->statistics as $key => $values) {
            $str = '<td>';
            
            $str .= '<table class="table table-condensed">';
            
            $str .= '<tr>';
            $str .= '<td style="white-space: nowrap;">Results found</td>';
            $str .= '<td style="white-space: nowrap;">';
            $str .= $values['resultsFound'] . '/' . count($this->getUserAgents());
            $str .= '</td>';
            $str .= '</tr>';
            
            /*
             * Browser
             */
            $str .= '<tr>';
            $str .= '<td style="white-space: nowrap;">Browser family</td>';
            $str .= '<td style="white-space: nowrap;">';
            $str .= $values['browser']['family'];
            $str .= '</td>';
            $str .= '</tr>';
            
            $str .= '<tr>';
            $str .= '<td style="white-space: nowrap;">Browser version</td>';
            $str .= '<td style="white-space: nowrap;">';
            $str .= $values['browser']['version'];
            $str .= '</td>';
            $str .= '</tr>';
            
            /*
             * OS
             */
            $str .= '<tr>';
            $str .= '<td style="white-space: nowrap;">OS family</td>';
            $str .= '<td style="white-space: nowrap;">';
            $str .= $values['operatingSystem']['family'];
            $str .= '</td>';
            $str .= '</tr>';
            
            $str .= '<tr>';
            $str .= '<td style="white-space: nowrap;">OS version</td>';
            $str .= '<td style="white-space: nowrap;">';
            $str .= $values['operatingSystem']['version'];
            $str .= '</td>';
            $str .= '</tr>';
            
            $str .= '<tr>';
            $str .= '<td style="white-space: nowrap;">OS platform</td>';
            $str .= '<td style="white-space: nowrap;">';
            $str .= $values['operatingSystem']['platform'];
            $str .= '</td>';
            $str .= '</tr>';
            
            /*
             * Device
             */
            $str .= '<tr>';
            $str .= '<td style="white-space: nowrap;">Device brand</td>';
            $str .= '<td style="white-space: nowrap;">';
            $str .= $values['device']['brand'];
            $str .= '</td>';
            $str .= '</tr>';
            
            $str .= '<tr>';
            $str .= '<td style="white-space: nowrap;">Device model</td>';
            $str .= '<td style="white-space: nowrap;">';
            $str .= $values['device']['model'];
            $str .= '</td>';
            $str .= '</tr>';
            
            $str .= '<tr>';
            $str .= '<td style="white-space: nowrap;">Device type</td>';
            $str .= '<td style="white-space: nowrap;">';
            $str .= $values['device']['type'];
            $str .= '</td>';
            $str .= '</tr>';
            
            $str .= '<tr>';
            $str .= '<td style="white-space: nowrap;">Is mobile</td>';
            $str .= '<td style="white-space: nowrap;">';
            $str .= $values['device']['isMobile'];
            $str .= '</td>';
            $str .= '</tr>';
            
            /*
             * Bots
             */
            $str .= '<tr>';
            $str .= '<td style="white-space: nowrap;">Is bot</td>';
            $str .= '<td style="white-space: nowrap;">';
            $str .= $values['bot']['isBot'];
            $str .= '</td>';
            $str .= '</tr>';
            
            $str .= '<tr>';
            $str .= '<td style="white-space: nowrap;">Bot name</td>';
            $str .= '<td style="white-space: nowrap;">';
            $str .= $values['bot']['name'];
            $str .= '</td>';
            $str .= '</tr>';
            
            $str .= '<tr>';
            $str .= '<td style="white-space: nowrap;">Bot type</td>';
            $str .= '<td style="white-space: nowrap;">';
            $str .= $values['bot']['type'];
            $str .= '</td>';
            $str .= '</tr>';
            
            
            $str .= '<tr>';
            $str .= '<td style="white-space: nowrap;">parseTime</td>';
            $str .= '<td style="white-space: nowrap;">';
            $str .= '<i class="glyphicon glyphicon-time"></i> ' . round($values['parseTime'], 5);
            $str .= '</td>';
            $str .= '</tr>';
            
            $str .= '</table>';
            $str .= '</td>';
            
            $table .= $str;
        }
        $table .= '</tr>';
        
        $table .= $tableRows;
        
        $table .= '</table>';
        
        return $table;
    }
}
