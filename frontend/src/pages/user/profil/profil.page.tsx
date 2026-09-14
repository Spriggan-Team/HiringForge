import React, { useEffect, useState } from 'react'

import UserQueriesServices from '../../../api/services/user/queries';
import { useAppContext, useCurrentUser } from '../../../hooks/context';

import EditProfilePage from './component/edit/edit.profile.page';
import ProfilePage from './component/view/profile.page';


import styles from './UserProfilPage.module.css'
import type { UserProfileData } from '../../../features/users/user.profile';


interface UserProfilPageProps
{}


const UserProfilPage: React.FC<UserProfilPageProps> = ({}) => {
    const user = useCurrentUser();
    const {setNavbar} = useAppContext();

    const [isEditing, setIsEditing] = useState(false);
    const [profile, setProfile] = useState<UserProfileData | null>(null);

    /**
     * ---------------
     *  Effects
     * ---------------
     */

    useEffect(()=>{
        setNavbar({ title: null, description: null });
        return ()=>{
            setNavbar(null)
        }
    },[])


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


    /**
     * --------------
     * Rendering
     * ---------------
     */

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




