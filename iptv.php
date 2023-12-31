<?php
/**
 * IPTV functions 
 * 
 * @author mkcdr
 * @copyright 2023
 */

/**
 * Load m3u playlist
 * 
 * @param string $m3uURL
 * @return array|false
 */
function iptv_load_playlist($m3uURL)
{
    if (filter_var($m3uURL, FILTER_VALIDATE_URL) && function_exists('curl_init'))
    {
        $ch = curl_init($m3uURL);
        curl_setopt($ch, CURLOPT_HEADER, false); 
        curl_setopt($ch, CURLOPT_NOBODY, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_exec($ch);
        
        if ($statusCode == 400)
        {
            return false;
        }
    }
    else
    {
        $content = @file_get_contents($m3uURL);
    }

    $delim = "\r\n";
    $channels = [];     // list of channels
    $tmpChannel = [];   // temp channel info to add

    // check if first line contain the right file header
    $line = strtok($content, $delim);

    if (strncmp($line, "#EXTM3U", 7)  !==  0)
    {
        return false;
    }

    // iterate line by line
    while (($line = strtok($delim)) !== false)
    {
        $line = trim($line);
        
        // check if current line is a comment
        if ($line[0] === '#')
        {
            $directive = '';
            $endOfDirective = 0;
            $colonIndex = strpos($line, ':');
            $dashesIndex = strpos($line, '--');

            if ($dashesIndex !== false)
            {
                if ($colonIndex !== false &&  $colonIndex < $dashesIndex)
                {
                    $endOfDirective = $colonIndex + 1;
                }
                else
                {
                    $endOfDirective = $dashesIndex + 2;
                }
            }
            elseif ($colonIndex !== false)
            {
                $endOfDirective = $colonIndex + 1;
            }
            else
            {
                continue;
            }

            $directive = substr($line, 0, $endOfDirective);
            $parameters = substr($line, strlen($directive));

            switch ($directive)
            {
                case '#EXTINF:':
                    $commaIndex = strrpos($parameters, ',');
                    $lastQuoteIndex = strrpos($parameters, '"');
                    
                    if ($lastQuoteIndex !== false)
                    {
                        $commaIndex = strpos($parameters, ',', $lastQuoteIndex + 1);
                    }

                    $tmpChannel['name'] = trim(substr($parameters, $commaIndex + 1));
                    $infoLine = substr($parameters, 0, $commaIndex);

                    $equalSignIndex = -1;
                    $infoLineLength = strlen($infoLine);
                    
                    while (($equalSignIndex = strpos($infoLine, '=', $equalSignIndex + 1)) !== false)
                    {
                        $propStartIndex = 0;
                        $spaceMarkIndex = strrpos($infoLine, ' ', $equalSignIndex - $infoLineLength - 1);
                        $quotationMarkIndex = strrpos($infoLine, '"', $equalSignIndex - $infoLineLength - 1);
                    
                        if ($spaceMarkIndex !== false)
                        {
                            $propStartIndex = $spaceMarkIndex;
                    
                            if ($quotationMarkIndex !== false)
                            {
                                if ($spaceMarkIndex > $quotationMarkIndex)
                                {
                                    $propStartIndex = $spaceMarkIndex;
                                }
                                else
                                {
                                    $propStartIndex = $quotationMarkIndex;
                                }
                            }
                        }
                        elseif ($quotationMarkIndex !== false)
                        {
                            $propStartIndex = $quotationMarkIndex;
                        }
                    
                        $prop = trim(substr($infoLine, $propStartIndex + 1, $equalSignIndex - $propStartIndex - 1));
                    
                        $find = ' ';
                    
                        if ($infoLine[$equalSignIndex + 1] == '"' )
                        {
                            $find = '"';
                            $equalSignIndex++;
                        }
                    
                        $valueEndIndex = strpos($infoLine, $find, $equalSignIndex + 1);
                        $value = trim(substr($infoLine, $equalSignIndex + 1, $valueEndIndex - $equalSignIndex - 1));
                    
                        $tmpChannel[$prop] = $value;
                    }
                    
                    break;
            }
        }
        else
        {
            // add channel to channels array
            $tmpChannel['url'] = $line;
            $channels[] = $tmpChannel;
            $tmpChannel = [];
        }
    }

    return $channels;
}