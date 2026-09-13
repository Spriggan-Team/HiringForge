import React, { useEffect, useState } from 'react'

import UserQueriesServices from '../../../api/services/user/queries';
import { useCurrentUser } from '../../../hooks/context';

import EditProfilePage from './component/edit/edit.profile.page';
import ProfilePage from './component/view/profile.page';


import styles from './UserProfilPage.module.css'
import type { UserProfileData } from '../../../features/users/user.profile';


interface UserProfilPageProps
{}


const UserProfilPage: React.FC<UserProfilPageProps> = ({}) => {
    const user = useCurrentUser();
    const [isEditing, setIsEditing] = useState(false);
    const [profile, setProfile] = useState<UserProfileData | null>(null);

    useEffect(()=> {
        const fetchData = async ()=>{
            try{
                const data = await UserQueriesServices.getProfileData({
                    companyId: user.company.id
                });
                console.log("DATA", data)
                setProfile(data);
            }
            catch(error){
                console.log("Something went wrong while retreiving profile data")
            }
        }
        fetchData();
    }, [user]);

    if(!profile){
        return (
            <div className={styles.placeholder}>
                <span>Loading</span>
            </div>
        )
    }

    return (
        <main className={styles.main}>
            <ProfilePage 
                data={profile}
                isEditing={isEditing}
                setIsEditing={setIsEditing}
            />
        </main>
    );
}
 
export default UserProfilPage;




