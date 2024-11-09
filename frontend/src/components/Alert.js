import React from 'react';
import {useAlert} from '../provider/AlertProvider';
import '../styles/Alert.css';

const Alert = () => {
    const {alert} = useAlert();

    if (!alert.visible) return null;

    return (
        <div className={`alert alert-${alert.type}`}>
            {alert.message}
        </div>
    );
};

export default Alert;
