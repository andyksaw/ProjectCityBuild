import * as React from 'react';
import * as Api from './api';
import * as Moment from 'moment';

interface InitialState {
};

interface Props {
    announcement: Api.ApiTopic,
    seed: number,
}

export default class Component extends React.Component<Props, InitialState> {
    state: InitialState = {
    }

    getRandom(min: number, max: number, localSeed: number = this.props.seed) : number {
        let x = Math.sin(localSeed) * 10000;
        x = x - Math.floor(x);

        x = (x * (max - min)) + min;

        return x;
    }

    renderAnnouncement(announcement: Api.ApiTopic) : React.ReactNode {
        // why do you do this to me, javascript...
        const username = (
            announcement.details && 
            announcement.details.details && 
            announcement.details.details.created_by &&
            announcement.details.details.created_by.username
        ) || "";

        const post: Api.ApiPost = 
            announcement.details &&
            announcement.details.post_stream &&
            announcement.details.post_stream.posts
                ? announcement.details.post_stream.posts[0]
                : { 
                    cooked: "", 
                };

        const date = Moment(announcement.created_at);

        return (
            <article className="article card" key={announcement.id}>
                <div className="article__container">
                    <h2 className="article__heading">{ announcement.title }</h2>
                    <div className="article__date">{ date.format('ddd, Do \of MMMM, YYYY') }</div>

                    <div className="article__body">
                        { 
                            announcement.details
                            ? post.cooked
                            : (
                                <div className="spinner">
                                    <div className="rect1"></div>
                                    <div className="rect2"></div>
                                    <div className="rect3"></div>
                                    <div className="rect4"></div>
                                    <div className="rect5"></div>
                                </div>
                            )
                        }
                    </div>

                    <div className="article__author">
                        Posted by
                        <img src={`https://minotar.net/helm/${username}/16`} width="16" alt={username} />
                        <a href="#">{ username }</a>
                    </div>
                </div>
                <div className="article__footer">
                    <div className="stats-container">
                        <div className="stat">
                            <span className="stat__figure">{ announcement.posts_count}</span>
                            <span className="stat__heading">Comments</span>
                        </div>
                        <div className="stat">
                            <span className="stat__figure">{ announcement.views }</span>
                            <span className="stat__heading">Post Views</span>
                        </div>
                    </div>
                    <div className="actions">
                        <a className="button button--accent button--large" href={`http://forums.projectcitybuild.com/t/${announcement.slug}/${announcement.id}`}>
                            Read Post
                            <i className="fa fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </article>
        );
    }

    renderSkeleton(index: number) : React.ReactNode {
        let bodySkeleton: Array<React.ReactNode> = [];
        for(let i = 0; i < this.getRandom(3, 6); i++) {
            bodySkeleton.push(
                <div key={i} 
                    className="skeleton" 
                    style={{width: this.getRandom(60, 100, this.props.seed + i) + '%'}}>
                </div>
            );
        }

        return (
            <article className="article card" key={index}>
                <div className="article__container">
                    <div className="skeleton skeleton--large article__heading" style={{width: this.getRandom(40, 80) + '%'}}></div>
                    <div className="skeleton article__date" style={{width:'20%'}}></div>
                    <div className="article__body">{ bodySkeleton }</div>

                    <div className="article__author">
                        <div className="skeleton-row">
                            <div className="skeleton" style={{width:'200px'}}></div>
                            <div className="skeleton skeleton--square skeleton--dark"></div>
                            <div className="skeleton"></div>
                        </div>
                    </div>
                </div>
                <div className="article__footer">
                    <div className="stats-container">
                        <div className="stat">
                            <span className="stat__figure">
                                <div className="skeleton skeleton--medium skeleton--dark skeleton--middle" style={{width:'15px'}}></div>
                            </span>
                            <span className="stat__heading">
                                <div className="skeleton skeleton--small" style={{width:'50px'}}></div>
                            </span>
                        </div>
                        <div className="stat">
                            <span className="stat__figure">
                                <div className="skeleton skeleton--medium skeleton--dark skeleton--middle" style={{width:'15px'}}></div>
                            </span>
                            <span className="stat__heading">
                                <div className="skeleton skeleton--small" style={{width:'50px'}}></div>
                            </span>
                        </div>
                    </div>
                    <div className="article__actions">
                        <div className="skeleton skeleton--button"></div>
                    </div>
                </div>
            </article>
        )
    }

    render() {
        if(!this.props.announcement || !this.props.announcement.details) {
            return this.renderSkeleton(0);
        }
        return this.renderAnnouncement(this.props.announcement);
    }
}