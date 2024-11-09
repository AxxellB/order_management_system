<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
    'react-chartjs-2' => [
        'version' => '5.2.0',
    ],
    'react' => [
        'version' => '18.3.1',
    ],
    'chart.js' => [
        'version' => '4.4.1',
    ],
    '@kurkle/color' => [
        'version' => '0.3.2',
    ],
    'react-datepicker' => [
        'version' => '7.5.0',
    ],
    'clsx' => [
        'version' => '2.1.1',
    ],
    'date-fns' => [
        'version' => '3.6.0',
    ],
    'date-fns/addDays' => [
        'version' => '3.6.0',
    ],
    'date-fns/addHours' => [
        'version' => '3.6.0',
    ],
    'date-fns/addMinutes' => [
        'version' => '3.6.0',
    ],
    'date-fns/addMonths' => [
        'version' => '3.6.0',
    ],
    'date-fns/addQuarters' => [
        'version' => '3.6.0',
    ],
    'date-fns/addSeconds' => [
        'version' => '3.6.0',
    ],
    'date-fns/addWeeks' => [
        'version' => '3.6.0',
    ],
    'date-fns/addYears' => [
        'version' => '3.6.0',
    ],
    'date-fns/differenceInCalendarDays' => [
        'version' => '3.6.0',
    ],
    'date-fns/differenceInCalendarMonths' => [
        'version' => '3.6.0',
    ],
    'date-fns/differenceInCalendarQuarters' => [
        'version' => '3.6.0',
    ],
    'date-fns/differenceInCalendarYears' => [
        'version' => '3.6.0',
    ],
    'date-fns/endOfDay' => [
        'version' => '3.6.0',
    ],
    'date-fns/endOfMonth' => [
        'version' => '3.6.0',
    ],
    'date-fns/endOfWeek' => [
        'version' => '3.6.0',
    ],
    'date-fns/endOfYear' => [
        'version' => '3.6.0',
    ],
    'date-fns/format' => [
        'version' => '3.6.0',
    ],
    'date-fns/getDate' => [
        'version' => '3.6.0',
    ],
    'date-fns/getDay' => [
        'version' => '3.6.0',
    ],
    'date-fns/getHours' => [
        'version' => '3.6.0',
    ],
    'date-fns/getISOWeek' => [
        'version' => '3.6.0',
    ],
    'date-fns/getMinutes' => [
        'version' => '3.6.0',
    ],
    'date-fns/getMonth' => [
        'version' => '3.6.0',
    ],
    'date-fns/getQuarter' => [
        'version' => '3.6.0',
    ],
    'date-fns/getSeconds' => [
        'version' => '3.6.0',
    ],
    'date-fns/getTime' => [
        'version' => '3.6.0',
    ],
    'date-fns/getYear' => [
        'version' => '3.6.0',
    ],
    'date-fns/isAfter' => [
        'version' => '3.6.0',
    ],
    'date-fns/isBefore' => [
        'version' => '3.6.0',
    ],
    'date-fns/isDate' => [
        'version' => '3.6.0',
    ],
    'date-fns/isEqual' => [
        'version' => '3.6.0',
    ],
    'date-fns/isSameDay' => [
        'version' => '3.6.0',
    ],
    'date-fns/isSameMonth' => [
        'version' => '3.6.0',
    ],
    'date-fns/isSameQuarter' => [
        'version' => '3.6.0',
    ],
    'date-fns/isSameYear' => [
        'version' => '3.6.0',
    ],
    'date-fns/isValid' => [
        'version' => '3.6.0',
    ],
    'date-fns/isWithinInterval' => [
        'version' => '3.6.0',
    ],
    'date-fns/max' => [
        'version' => '3.6.0',
    ],
    'date-fns/min' => [
        'version' => '3.6.0',
    ],
    'date-fns/parse' => [
        'version' => '3.6.0',
    ],
    'date-fns/parseISO' => [
        'version' => '3.6.0',
    ],
    'date-fns/set' => [
        'version' => '3.6.0',
    ],
    'date-fns/setHours' => [
        'version' => '3.6.0',
    ],
    'date-fns/setMinutes' => [
        'version' => '3.6.0',
    ],
    'date-fns/setMonth' => [
        'version' => '3.6.0',
    ],
    'date-fns/setQuarter' => [
        'version' => '3.6.0',
    ],
    'date-fns/setSeconds' => [
        'version' => '3.6.0',
    ],
    'date-fns/setYear' => [
        'version' => '3.6.0',
    ],
    'date-fns/startOfDay' => [
        'version' => '3.6.0',
    ],
    'date-fns/startOfMonth' => [
        'version' => '3.6.0',
    ],
    'date-fns/startOfQuarter' => [
        'version' => '3.6.0',
    ],
    'date-fns/startOfWeek' => [
        'version' => '3.6.0',
    ],
    'date-fns/startOfYear' => [
        'version' => '3.6.0',
    ],
    'date-fns/subDays' => [
        'version' => '3.6.0',
    ],
    'date-fns/subMonths' => [
        'version' => '3.6.0',
    ],
    'date-fns/subQuarters' => [
        'version' => '3.6.0',
    ],
    'date-fns/subWeeks' => [
        'version' => '3.6.0',
    ],
    'date-fns/subYears' => [
        'version' => '3.6.0',
    ],
    'date-fns/toDate' => [
        'version' => '3.6.0',
    ],
    '@floating-ui/react' => [
        'version' => '0.26.25',
    ],
    'react-dom' => [
        'version' => '18.2.0',
    ],
    'react-datepicker/dist/react-datepicker.min.css' => [
        'version' => '7.5.0',
        'type' => 'css',
    ],
    '@floating-ui/react/utils' => [
        'version' => '0.26.25',
    ],
    '@floating-ui/utils' => [
        'version' => '0.2.8',
    ],
    '@floating-ui/utils/dom' => [
        'version' => '0.2.8',
    ],
    'tabbable' => [
        'version' => '6.2.0',
    ],
    '@floating-ui/react-dom' => [
        'version' => '2.1.2',
    ],
    'scheduler' => [
        'version' => '0.23.0',
    ],
    '@floating-ui/dom' => [
        'version' => '1.6.11',
    ],
    '@floating-ui/core' => [
        'version' => '1.6.8',
    ],
    'axios' => [
        'version' => '1.7.7',
    ],
    'recharts' => [
        'version' => '2.13.3',
    ],
    'lodash/get' => [
        'version' => '4.17.21',
    ],
    'lodash/isNil' => [
        'version' => '4.17.21',
    ],
    'lodash/isString' => [
        'version' => '4.17.21',
    ],
    'lodash/isFunction' => [
        'version' => '4.17.21',
    ],
    'lodash/isObject' => [
        'version' => '4.17.21',
    ],
    'react-is' => [
        'version' => '18.3.1',
    ],
    'lodash/isNaN' => [
        'version' => '4.17.21',
    ],
    'lodash/isNumber' => [
        'version' => '4.17.21',
    ],
    'lodash/upperFirst' => [
        'version' => '4.17.21',
    ],
    'victory-vendor/d3-shape' => [
        'version' => '36.9.2',
    ],
    'lodash/uniqBy' => [
        'version' => '4.17.21',
    ],
    'lodash/sortBy' => [
        'version' => '4.17.21',
    ],
    'lodash/throttle' => [
        'version' => '4.17.21',
    ],
    'victory-vendor/d3-scale' => [
        'version' => '36.9.2',
    ],
    'lodash/max' => [
        'version' => '4.17.21',
    ],
    'lodash/min' => [
        'version' => '4.17.21',
    ],
    'lodash/flatMap' => [
        'version' => '4.17.21',
    ],
    'lodash/isEqual' => [
        'version' => '4.17.21',
    ],
    'recharts-scale' => [
        'version' => '0.4.5',
    ],
    'tiny-invariant' => [
        'version' => '1.3.3',
    ],
    'lodash/last' => [
        'version' => '4.17.21',
    ],
    'react-smooth' => [
        'version' => '4.0.1',
    ],
    'lodash/maxBy' => [
        'version' => '4.17.21',
    ],
    'lodash/minBy' => [
        'version' => '4.17.21',
    ],
    'lodash/isPlainObject' => [
        'version' => '4.17.21',
    ],
    'lodash/isBoolean' => [
        'version' => '4.17.21',
    ],
    'lodash/first' => [
        'version' => '4.17.21',
    ],
    'lodash/range' => [
        'version' => '4.17.21',
    ],
    'lodash/some' => [
        'version' => '4.17.21',
    ],
    'lodash/mapValues' => [
        'version' => '4.17.21',
    ],
    'lodash/every' => [
        'version' => '4.17.21',
    ],
    'lodash/find' => [
        'version' => '4.17.21',
    ],
    'lodash/memoize' => [
        'version' => '4.17.21',
    ],
    'eventemitter3' => [
        'version' => '4.0.7',
    ],
    'lodash/omit' => [
        'version' => '4.17.21',
    ],
    'lodash/sumBy' => [
        'version' => '4.17.21',
    ],
    'd3-shape' => [
        'version' => '3.2.0',
    ],
    'd3-scale' => [
        'version' => '4.0.2',
    ],
    'decimal.js-light' => [
        'version' => '2.5.1',
    ],
    'prop-types' => [
        'version' => '15.8.1',
    ],
    'fast-equals' => [
        'version' => '5.0.1',
    ],
    'react-transition-group' => [
        'version' => '4.4.5',
    ],
    'd3-path' => [
        'version' => '3.1.0',
    ],
    'd3-array' => [
        'version' => '3.2.4',
    ],
    'd3-interpolate' => [
        'version' => '3.0.1',
    ],
    'd3-format' => [
        'version' => '3.1.0',
    ],
    'd3-time' => [
        'version' => '3.1.0',
    ],
    'd3-time-format' => [
        'version' => '4.1.0',
    ],
    '@babel/runtime/helpers/esm/extends' => [
        'version' => '7.23.8',
    ],
    '@babel/runtime/helpers/esm/objectWithoutPropertiesLoose' => [
        'version' => '7.23.8',
    ],
    '@babel/runtime/helpers/esm/inheritsLoose' => [
        'version' => '7.23.8',
    ],
    'dom-helpers/addClass' => [
        'version' => '5.2.1',
    ],
    'dom-helpers/removeClass' => [
        'version' => '5.2.1',
    ],
    '@babel/runtime/helpers/esm/assertThisInitialized' => [
        'version' => '7.23.8',
    ],
    'internmap' => [
        'version' => '2.0.3',
    ],
    'd3-color' => [
        'version' => '3.1.0',
    ],
];
