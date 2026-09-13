import React from 'react'

import type { UserProfileData } from '../../../../../features/users/user.profile';

import styles from './EditProfilePage.module.css'


interface EditProfilePageProps{
    defaultData: UserProfileData;
    setIsEditing: React.Dispatch<React.SetStateAction<boolean>>;
    setProfile: React.Dispatch<React.SetStateAction<UserProfileData | null>>;
}

/** Editing Mode */
const EditProfilePage: React.FC<EditProfilePageProps> = ({
    defaultData,
    setProfile
}) => {
    return (
        <div className={styles.container}>

        </div>
    );
}
 
export default EditProfilePage;