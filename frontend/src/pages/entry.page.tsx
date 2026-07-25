


interface EntryPageProps{
    children: React.ReactNode
}

const EntryPage: React.FC<EntryPageProps> = ({
    children
}) => {
    return (
        <>
            {children}
        </>
    );
}
 
export default EntryPage;