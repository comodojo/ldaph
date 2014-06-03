<?php namespace comodojo;

/**
 * standard spare parts debug classes
 * 
 * @package 	Comodojo Spare Parts
 * @author		comodojo.org
 * @copyright 	__COPYRIGHT__ comodojo.org (info@comodojo.org)
 * @version 	__CURRENT_VERSION__
 *
 * LICENSE:
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Write log to file or standard php error log
 * 
 * WARNING: using a custom error log file is actually highly inefficient,
 * it open/close file at each call.
 *
 * This behaviour will be changed in future releases.
 */
function debug_line($log) {

	if (is_null(COMODOJO_GLOBAL_DEBUG_FILE)){
		error_log($log);
	}
	else {
		$file = COMODOJO_GLOBAL_DEBUG_FILE;
		$file_handler = fopen($file, 'a');
		if (!$file_handler) return false;
		fwrite($file_handler, date(DATE_RFC822)." - ".$log."\n");
		fclose($file_handler);
	}
}

/**
 * Debug something to error_log
 * 
 * @param	string|object|array|integer	$message	Debug message
 * @param	string						$type		The message type (INFO|WARNING|ERROR)
 * @param	string						$reference	The message reference (i.e. DATABASE, SSH, ...)
 */
function debug($message,$type='ERROR',$reference="UNKNOWN") {

	if (COMODOJO_GLOBAL_DEBUG_ENABLED) {

		if ( strtoupper(COMODOJO_GLOBAL_DEBUG_LEVEL) == 'ERROR' AND strtoupper($type) != 'ERROR') return;
		elseif ( strtoupper(COMODOJO_GLOBAL_DEBUG_LEVEL) == 'WARNING' AND (strtoupper($type) != 'ERROR' OR strtoupper($type) != 'WARNING')) return;
		elseif ( is_array($message) OR is_object($message) ) {
			debug_line("(".$type.") ".$reference." ------ Start of debug dump ------");
			debug_line(var_export($message, true));
			debug_line("(".$type.") ".$reference." ------ End of debug dump ------");
		}
		elseif(is_scalar($message)) {
			debug_line("(".$type.") ".$reference." | ".$message);
		}
		else {
			debug_line("(DEBUG-ERROR): invalid value type for debug.");
		}

	}

}