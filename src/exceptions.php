<?php

namespace Genesis {

	class Exception extends \Exception {}

	class TerminateException extends Exception {}

	class ErrorException extends Exception {}

	class InvalidArgumentException extends Exception {}

	class InvalidStateException extends Exception {}

	class NotSupportedException extends Exception {}

	class ContainerFactoryException extends Exception {}

	class MemberAccessException extends Exception {}

}

namespace {
	if (PHP_VERSION_ID < 70000) { // backwards compatibility

		class Throwable extends \Exception{} // it's actually interface, but this is shortcut
	}
}